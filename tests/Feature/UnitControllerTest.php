<?php

use App\Enums\ProductUnit;
use App\Helpers\UnitManager;

it('gets all unit categories', function () {
    $response = $this->getJson('/api/units/categories');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => UnitManager::getCategories()
        ]);
});

it('gets all units and options', function () {
    $response = $this->getJson('/api/units/all');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'units',
                'options'
            ]
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'units' => ProductUnit::values(),
                'options' => ProductUnit::arabicOptions()
            ]
        ]);
});

it('gets units by category', function () {
    $category = 'weight';
    $response = $this->getJson("/api/units/category/{$category}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'units',
                'options',
                'category'
            ]
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'category' => $category
            ]
        ]);
});

it('gets unit info', function () {
    $unit = ProductUnit::KILOGRAM->value;
    $response = $this->getJson("/api/units/info/{$unit}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'unit',
                'category',
                'balance',
                'scales',
                'recommended_unit',
                'validation_rules',
                'compatible_units'
            ]
        ])
        ->assertJson([
            'success' => true
        ]);
});

it('gets scale options for category', function () {
    $category = 'weight';
    $response = $this->getJson("/api/units/scales/{$category}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'scales',
                'category'
            ]
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'category' => $category
            ]
        ]);
});

it('gets balance info for category', function () {
    $category = 'weight';
    $response = $this->getJson("/api/units/balance/{$category}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'category',
                'base_unit',
                'precision',
                'min_value',
                'max_value',
                'default_scale',
                'unit_label',
                'unit_symbol'
            ]
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'category' => $category
            ]
        ]);
});

it('gets unit selection data', function () {
    $response = $this->getJson('/api/units/selection-data');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'weight',
                'volume',
                'length',
                'count'
            ]
        ])
        ->assertJson([
            'success' => true
        ]);
});

it('processes unit selection successfully', function () {
    $response = $this->postJson('/api/units/process-selection', [
        'unit' => ProductUnit::KILOGRAM->value,
        'quantity' => 2.5
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'unit',
                'category',
                'quantity',
                'formatted_quantity',
                'balance',
                'recommended_scale',
                'validation',
                'compatible_units',
                'scales'
            ]
        ])
        ->assertJson([
            'success' => true,
            'data' => [
                'unit' => ProductUnit::KILOGRAM->value,
                'quantity' => 2.5
            ]
        ]);
});

it('fails processing unit selection with invalid inputs', function () {
    $response = $this->postJson('/api/units/process-selection', [
        'unit' => '',
        'quantity' => -1
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['unit', 'quantity']);
});

it('calculates total price correctly', function () {
    $response = $this->postJson('/api/units/calculate-price', [
        'quantity' => 5,
        'unit_price' => 10.5,
        'unit' => ProductUnit::PIECE->value
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'quantity' => 5,
                'unit_price' => 10.5,
                'unit' => ProductUnit::PIECE->value,
                'total_price' => 52.5
            ]
        ]);
});

it('fails total price calculation with invalid inputs', function () {
    $response = $this->postJson('/api/units/calculate-price', [
        'quantity' => -1,
        'unit_price' => '',
        'unit' => ''
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quantity', 'unit_price', 'unit']);
});

it('converts quantity successfully between compatible units', function () {
    $response = $this->postJson('/api/units/convert', [
        'quantity' => 2,
        'from_unit' => ProductUnit::KILOGRAM->value,
        'to_unit' => ProductUnit::GRAM->value
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'original_quantity' => 2,
                'original_unit' => ProductUnit::KILOGRAM->value,
                'converted_quantity' => 2000,
                'converted_unit' => ProductUnit::GRAM->value
            ]
        ]);
});

it('fails converting quantity between incompatible units', function () {
    $response = $this->postJson('/api/units/convert', [
        'quantity' => 2,
        'from_unit' => ProductUnit::KILOGRAM->value,
        'to_unit' => ProductUnit::LITER->value
    ]);

    $response->assertStatus(400)
        ->assertJson([
            'success' => false
        ]);
});

it('fails converting quantity with invalid inputs', function () {
    $response = $this->postJson('/api/units/convert', [
        'quantity' => '',
        'from_unit' => '',
        'to_unit' => ''
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quantity', 'from_unit', 'to_unit']);
});

it('validates quantity successfully', function () {
    $response = $this->postJson('/api/units/validate', [
        'quantity' => 500,
        'category' => 'weight'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_valid' => true,
                'quantity' => 500,
                'category' => 'weight'
            ]
        ]);
});

it('validates quantity as invalid when bounds exceeded', function () {
    $response = $this->postJson('/api/units/validate', [
        'quantity' => 99999999, // exceed max limit
        'category' => 'weight'
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [
                'is_valid' => false,
                'quantity' => 99999999,
                'category' => 'weight'
            ]
        ]);
});

it('fails quantity validation with invalid inputs', function () {
    $response = $this->postJson('/api/units/validate', [
        'quantity' => '',
        'category' => ''
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['quantity', 'category']);
});
