<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Product\DTOs\ProductData;
use App\Domain\Product\DTOs\ProviderMappingData;
use App\Domain\Product\DTOs\TenantProductData;
use App\Domain\Product\Enums\ProductCapability;
use App\Domain\Product\Enums\ProductStatus;
use App\Domain\Product\Enums\ProviderMappingStatus;
use App\Domain\Product\Models\Product;
use App\Domain\Product\Models\ProductCategory;
use App\Domain\Product\Models\ProductProviderMapping;
use App\Domain\Product\Models\TenantProduct;
use App\Domain\Product\Services\ProductCapabilityService;
use App\Domain\Product\Services\ProductCategoryService;
use App\Domain\Product\Services\ProductProviderMappingService;
use App\Domain\Product\Services\ProductProviderResolver;
use App\Domain\Product\Services\ProductService;
use App\Domain\Product\Services\TenantProductService;
use App\Domain\Tenancy\Models\Tenant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreProductCategoryRequest;
use App\Http\Requests\Platform\StoreProductRequest;
use App\Http\Requests\Platform\StoreProviderMappingRequest;
use App\Http\Requests\Platform\StoreTenantProductRequest;
use App\Http\Requests\Platform\UpdateProductCapabilitiesRequest;
use App\Http\Requests\Platform\UpdateProductRequest;
use App\Http\Requests\Platform\UpdateProductStatusRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $products,
        private readonly ProductCategoryService $categories,
        private readonly ProductProviderMappingService $providerMappings,
        private readonly ProductProviderResolver $providerResolver,
        private readonly ProductCapabilityService $capabilities,
        private readonly TenantProductService $tenantProducts,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Product::query()
            ->with(['category', 'providerMappings', 'capabilities'])
            ->when($request->filled('search'), fn ($builder) => $builder->where(fn ($inner) => $inner
                ->where('name', 'like', '%'.$request->query('search').'%')
                ->orWhere('code', 'like', '%'.$request->query('search').'%')))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->query('status')))
            ->when($request->filled('category'), fn ($builder) => $builder->where('product_category_id', $request->query('category')))
            ->latest();

        return view('platform.products.index', [
            'products' => $query->paginate(15)->withQueryString(),
            'categories' => ProductCategory::query()->orderBy('display_order')->orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'category']),
        ]);
    }

    public function create(): View
    {
        return view('platform.products.create', [
            'categories' => ProductCategory::query()->where('status', 'active')->orderBy('name')->get(),
            'capabilities' => ProductCapability::cases(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->products->create($this->productData($request->validated()));

        return redirect()
            ->route('platform.products.edit', $product)
            ->with('status', 'Product created successfully.');
    }

    public function edit(Product $product): View
    {
        return view('platform.products.edit', [
            'product' => $product->load(['category', 'providerMappings', 'capabilities', 'tenantProducts.tenant']),
            'categories' => ProductCategory::query()->where('status', 'active')->orderBy('name')->get(),
            'capabilities' => ProductCapability::cases(),
            'capabilityValues' => $this->capabilities->all($product->id),
            'resolvedProvider' => $this->providerResolver->resolve($product->id),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update($product, $this->productData([
            ...$request->validated(),
            'code' => $product->code,
        ]));

        return back()->with('status', 'Product updated successfully.');
    }

    public function status(UpdateProductStatusRequest $request, Product $product): RedirectResponse
    {
        $request->validated('status') === ProductStatus::ACTIVE->value
            ? $this->products->activate($product)
            : $this->products->disable($product);

        return back()->with('status', 'Product status updated successfully.');
    }

    public function categories(): View
    {
        return view('platform.products.categories', [
            'categories' => ProductCategory::query()->withCount('products')->orderBy('display_order')->orderBy('name')->paginate(20),
            'defaults' => ['Identity Verification', 'Wallet', 'Payment', 'Airtime', 'Data', 'Cable TV', 'Electricity', 'Insurance', 'Government Services'],
        ]);
    }

    public function storeCategory(StoreProductCategoryRequest $request): RedirectResponse
    {
        $this->categories->create(
            code: $request->validated('code'),
            name: $request->validated('name'),
            description: $request->validated('description'),
            displayOrder: (int) ($request->validated('display_order') ?? 100),
        );

        return back()->with('status', 'Product category created successfully.');
    }

    public function providerMappings(): View
    {
        return view('platform.products.provider-mappings', [
            'products' => Product::query()->orderBy('name')->get(),
            'mappings' => ProductProviderMapping::query()->with('product')->latest()->paginate(20),
        ]);
    }

    public function storeProviderMapping(StoreProviderMappingRequest $request): RedirectResponse
    {
        $mapping = $this->providerMappings->map(new ProviderMappingData(
            productId: $request->validated('product_id'),
            provider: $request->validated('provider'),
            providerProductCode: $request->validated('provider_product_code'),
            priority: (int) ($request->validated('priority') ?? 100),
        ));

        if (($request->validated('status') ?? 'active') === ProviderMappingStatus::DISABLED->value) {
            $this->providerMappings->updateStatus($mapping, ProviderMappingStatus::DISABLED);
        }

        return back()->with('status', 'Provider mapping saved successfully.');
    }

    public function providerMappingStatus(Request $request, ProductProviderMapping $mapping): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:active,disabled']]);
        $this->providerMappings->updateStatus($mapping, ProviderMappingStatus::from($validated['status']));

        return back()->with('status', 'Provider mapping status updated successfully.');
    }

    public function updateCapabilities(UpdateProductCapabilitiesRequest $request): RedirectResponse
    {
        $selected = collect($request->validated('capabilities', []))->values()->all();

        foreach (ProductCapability::cases() as $capability) {
            $this->capabilities->set(
                productId: $request->validated('product_id'),
                capabilityKey: $capability->value,
                enabled: in_array($capability->value, $selected, true),
            );
        }

        return back()->with('status', 'Product capabilities updated successfully.');
    }

    public function tenantProducts(): View
    {
        return view('platform.products.tenant-products', [
            'tenants' => Tenant::query()->orderBy('name')->get(),
            'products' => Product::query()->orderBy('name')->get(),
            'overrides' => TenantProduct::query()->with(['tenant', 'product'])->latest()->paginate(20),
        ]);
    }

    public function storeTenantProduct(StoreTenantProductRequest $request): RedirectResponse
    {
        $this->tenantProducts->configure(new TenantProductData(
            tenantId: $request->validated('tenant_id'),
            productId: $request->validated('product_id'),
            isEnabled: (bool) $request->boolean('is_enabled'),
            sellingPriceOverride: $request->validated('selling_price_override'),
            statusOverride: $request->validated('status_override'),
            metadata: ['visibility' => $request->validated('visibility')],
        ));

        return back()->with('status', 'Tenant product override saved successfully.');
    }

    private function productData(array $data): ProductData
    {
        return new ProductData(
            categoryId: $data['product_category_id'],
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? '',
            defaultPrice: (string) $data['default_price'],
            costPrice: isset($data['cost_price']) ? (string) $data['cost_price'] : null,
            sellingPrice: isset($data['selling_price']) ? (string) $data['selling_price'] : null,
            currency: $data['currency'],
            visibility: $data['visibility'],
        );
    }
}
