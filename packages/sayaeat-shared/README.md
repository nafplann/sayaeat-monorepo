# SayaEat Shared Package

This package contains shared code used across SayaEat BFF services.

## Contents

- **DTOs**: Data Transfer Objects for API communication
- **Enums**: Business enums (OrderStatus, ServiceEnum, etc.)
- **Utils**: Shared utility functions (DistanceCalculator, FeeCalculator, etc.)
- **Contracts**: Interfaces for services
- **Clients**: HTTP clients (PyramidClient)

## Installation

This package is used internally in the monorepo:

```bash
composer require sayaeat/shared:@dev
```

## Usage

```php
use SayaEat\Shared\Enums\OrderPaymentStatus;
use SayaEat\Shared\Clients\PyramidClient;
use SayaEat\Shared\Utils\DistanceCalculator;

// Use enums
$status = OrderPaymentStatus::PAID;

// Use Pyramid client
$client = new PyramidClient($baseUrl, $apiKey);
$merchants = $client->get('internal/merchants');

// Use utilities
$distance = DistanceCalculator::calculate($lat1, $lon1, $lat2, $lon2);
```

## Development

```bash
# Install dependencies
composer install

# Run tests
vendor/bin/pest
```

