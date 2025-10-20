<?php

namespace App;

use JsonSerializable;

class Product implements JsonSerializable
{
    private int $id;
    private string $customerName;
    private string $serviceName;
    private int $accountId;
    private ?int $createdAt;
    private ?int $expiredAt;

    private ?array $details;
    // Nouvelles propriétés pour les données enrichies
    private ?int $diskUsage = null;
    private ?int $diskLimit = null;
    private ?int $sslExpiresAt = null;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->customerName = $data['customer_name'];
        $this->serviceName = $data['service_name'];
        $this->accountId = $data['account_id'];
        $this->createdAt = $data['created_at'] ?? null;
        $this->expiredAt = $data['expired_at'] ?? null;

        $this->details = $data['details'] ?? null;
        // Traitement des détails enrichis
        if (isset($data['details'])) {
            $details = $data['details'];
            $this->diskUsage = $details['quota']['disk_usage'] ?? null;
            $this->diskLimit = $details['quota']['disk_limit'] ?? null;
            $this->sslExpiresAt = isset($details['ssl']['expires_on']) ? strtotime($details['ssl']['expires_on']) : null;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCustomerName(): string
    {
        return htmlspecialchars($this->customerName);
    }

    public function getServiceName(): string
    {
        return htmlspecialchars($this->serviceName);
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

    // --- Méthodes pour le stockage ---

    public function getFormattedDiskUsage(): string
    {
        if ($this->diskUsage === null || $this->diskLimit === null || $this->diskLimit === 0) {
            return 'N/A';
        }

        $usageGo = round($this->diskUsage / 1024 / 1024 / 1024, 2);
        $limitGo = round($this->diskLimit / 1024 / 1024 / 1024, 2);
        $percentage = round(($this->diskUsage / $this->diskLimit) * 100);

        return "{$usageGo} / {$limitGo} Go ({$percentage}%)";
    }

    public function getDiskUsageStatusBadge(): string
    {
        if ($this->diskUsage === null || $this->diskLimit === null || $this->diskLimit === 0) {
            return ''; // Pas de badge si pas de données
        }

        $percentage = ($this->diskUsage / $this->diskLimit) * 100;
        $class = 'bg-secondary';

        if ($percentage < 75) {
            $class = 'bg-success';
        } elseif ($percentage < 90) {
            $class = 'bg-warning';
        } else {
            $class = 'bg-danger';
        }

        return "<span class=\"badge {$class}\">{$this->getFormattedDiskUsage()}</span>";
    }

    // --- Méthodes pour les expirations (Produit, Domaine, SSL) ---

    private function getExpirationStatus(string $label, ?int $timestamp): string
    {
        if ($timestamp === null) {
            return ''; // Pas de badge si pas de date
        }

        $daysRemaining = floor(($timestamp - time()) / (60 * 60 * 24));
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

    public function getSslExpirationStatusBadge(): string
    {
        return $this->getExpirationStatus('SSL', $this->sslExpiresAt);
    }

    /**
     * Specifies which data should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        // Returns all object properties as an array
        $vars = get_object_vars($this);
        // On ajoute les valeurs formatées pour un accès facile en JS
        $vars['formattedDiskUsage'] = $this->getFormattedDiskUsage();
        $vars['productExpirationStatus'] = strip_tags($this->getProductExpirationStatusBadge());
        $vars['sslExpirationStatus'] = strip_tags($this->getSslExpirationStatusBadge());
        $vars['formattedCreatedAt'] = $this->getFormattedCreatedAt();
        $vars['formattedExpiredAt'] = $this->getFormattedExpiredAt();

        return $vars;
    }
}
