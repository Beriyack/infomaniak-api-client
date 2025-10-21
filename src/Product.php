<?php

namespace App;

use JsonSerializable;

/**
 * 
 *
 * @author Beriyack
 * @version 1.0.0
 */
class Product implements JsonSerializable
{
    private int $id;
    private string $customerName;
    private string $serviceName;
    private int $accountId;
    private ?int $createdAt;
    private ?int $expiredAt;
    protected ?array $details;
    protected array $rawData;

    public function __construct(array $data)
    {
        $this->rawData = $data; // Conserve les données brutes pour la sérialisation et le débogage
        $this->id = $data['id'];
        $this->customerName = $data['customer_name'];
        $this->serviceName = $data['service_name'];
        $this->accountId = $data['account_id'];
        $this->createdAt = $data['created_at'] ?? null;
        $this->expiredAt = $data['expired_at'] ?? null;
        $this->details = $data['details'] ?? null; // Peut contenir des infos comme le FQDN
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getAccountId(): int
    {
        return $this->accountId;
    }

    public function getFormattedCreatedAt(string $format = 'd/m/Y'): string
    {
        if ($this->createdAt === null) {
            return 'N/A';
        }
        return date($format, $this->createdAt);
    }

    public function getFormattedExpiredAt(string $format = 'd/m/Y'): string
    {
        if ($this->expiredAt === null) {
            return 'N/A';
        }
        return date($format, $this->expiredAt);
    }

    // --- Méthodes pour les expirations (Produit, Domaine, SSL) ---

    private function getExpirationStatus(string $label, ?int $timestamp): string
    {
        if ($timestamp === null) {
            return ''; // Pas de badge si pas de date
        }

        $daysRemaining = $this->getDaysUntilTimestamp($timestamp);
        $class = 'bg-success'; // Vert par défaut

        if ($daysRemaining < 0) {
            $class = 'bg-danger';
            $statusText = 'Expiré';
        } elseif ($daysRemaining <= 30) {
            $class = 'bg-warning';
            $statusText = "Expire dans {$daysRemaining} j";
        } else {
            $statusText = 'OK';
        }

        $date = date('d/m/Y', $timestamp);
        return "<span class=\"badge {$class}\" title=\"{$date}\">{$label}: {$statusText}</span>";
    }

    public function getProductExpirationStatusBadge(): string
    {
        return $this->getExpirationStatus('Produit', $this->expiredAt);
    }

    private function getDaysUntilTimestamp(?int $timestamp): ?int
    {
        if ($timestamp === null) {
            return null;
        }
        return (int) floor(($timestamp - time()) / 86400); // 86400 = 60 * 60 * 24
    }

    public function getDaysUntilProductExpiration(): ?int
    {
        return $this->getDaysUntilTimestamp($this->expiredAt);
    }

    /**
     * Détermine si le produit est dans un état "critique" (expiration proche).
     * @return bool
     */
    public function isCritical(): bool
    {
        $productDays = $this->getDaysUntilProductExpiration();

        // Un produit est critique si son expiration est dans 30 jours ou moins.
        return $productDays !== null && $productDays <= 30;
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    /**
     * Specifies which data should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        // Retourne les données brutes originales, ce qui est plus utile pour le débogage.
        return $this->rawData;
    }
}
