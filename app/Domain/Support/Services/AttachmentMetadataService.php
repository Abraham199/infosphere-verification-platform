<?php

namespace App\Domain\Support\Services;

use App\Domain\Support\DTOs\AttachmentData;
use App\Domain\Support\Exceptions\InvalidSupportConfigurationException;
use App\Domain\Support\Interfaces\SupportRepositoryInterface;
use App\Domain\Support\Models\SupportTicketAttachment;

class AttachmentMetadataService
{
    public function __construct(private readonly SupportRepositoryInterface $support)
    {
    }

    public function create(AttachmentData $data): SupportTicketAttachment
    {
        if ($this->support->findTicket($data->ticketId) === null) {
            throw new InvalidSupportConfigurationException('Support ticket not found for attachment.');
        }

        if ($data->sizeBytes <= 0 || trim($data->filename) === '' || trim($data->storagePath) === '') {
            throw new InvalidSupportConfigurationException('Attachment metadata is incomplete.');
        }

        return $this->support->createAttachment([
            'support_ticket_id' => $data->ticketId,
            'uploaded_by' => $data->uploadedBy,
            'filename' => $data->filename,
            'mime_type' => $data->mimeType,
            'size_bytes' => $data->sizeBytes,
            'storage_path' => $data->storagePath,
            'metadata' => $data->metadata,
        ]);
    }
}
