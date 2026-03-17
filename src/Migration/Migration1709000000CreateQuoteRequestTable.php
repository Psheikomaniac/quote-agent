<?php

declare(strict_types=1);

namespace QuoteAgent\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

final class Migration1709000000CreateQuoteRequestTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1709000000;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `b2b_quote_request` (
                `id`               BINARY(16)    NOT NULL,
                `quote_number`     VARCHAR(64)   NOT NULL,
                `customer_id`      BINARY(16)    NOT NULL,
                `status`           VARCHAR(32)   NOT NULL DEFAULT \'new\',
                `items`            JSON,
                `customer_message` LONGTEXT,
                `ai_text`          LONGTEXT,
                `total_amount`     DOUBLE,
                `valid_until`      DATETIME(3),
                `accept_token`     VARCHAR(64)   NOT NULL,
                `pdf_path`         VARCHAR(512),
                `created_at`       DATETIME(3)   NOT NULL,
                `updated_at`       DATETIME(3),
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq.b2b_quote_request.quote_number` (`quote_number`),
                UNIQUE KEY `uniq.b2b_quote_request.accept_token` (`accept_token`),
                CONSTRAINT `fk.b2b_quote_request.customer_id`
                    FOREIGN KEY (`customer_id`)
                    REFERENCES `customer` (`id`)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }

    public function updateDestructive(Connection $connection): void
    {
        // Drop logic is handled in QuoteAgent::uninstall() to prevent accidental
        // data loss when operators run database:migrate-destructive for other plugins.
    }
}
