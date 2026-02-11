<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;

trait AutoFillsFromProduct
{
    protected static function bootAutoFillsFromProduct(): void
    {
        static::creating(function (Model $model) {
            if ($model->product_id && !$model->relationLoaded('product')) {
                $model->load('product');
            }
            
            if ($model->product) {
                $model->fillFromProduct();
            }
        });

        static::updating(function (Model $model) {
            // If product_id changed, update related fields
            if ($model->isDirty('product_id')) {
                if ($model->product_id && !$model->relationLoaded('product')) {
                    $model->load('product');
                }
                
                if ($model->product) {
                    $model->fillFromProduct();
                }
            }
        });
    }

    public function fillFromProduct(): void
    {
        if (!$this->product) {
            return;
        }

        $fieldsToFill = [
            'product_code' => 'product_code',
            'customer_name' => 'customer_name',
            'model_name' => 'model_name',
            'description' => 'description',
            'dimension' => 'dimension',
            'uom' => 'uom',
        ];

        foreach ($fieldsToFill as $modelField => $productField) {
            if (in_array($modelField, $this->fillable) && empty($this->$modelField)) {
                $this->$modelField = $this->product->$productField;
            }
        }
    }

    public function refreshFromProduct(): self
    {
        if ($this->product_id) {
            $this->load('product');
            
            if ($this->product) {
                $fieldsToFill = [
                    'product_code' => 'product_code',
                    'customer_name' => 'customer_name',
                    'model_name' => 'model_name',
                    'description' => 'description',
                    'dimension' => 'dimension',
                    'uom' => 'uom',
                ];

                foreach ($fieldsToFill as $modelField => $productField) {
                    if (in_array($modelField, $this->fillable)) {
                        $this->$modelField = $this->product->$productField;
                    }
                }
                
                $this->save();
            }
        }

        return $this;
    }

    public function isProductDataInSync(): bool
    {
        if (!$this->product) {
            return false;
        }

        $fieldsToCheck = [
            'product_code' => 'product_code',
            'customer_name' => 'customer_name',
            'model_name' => 'model_name',
            'description' => 'description',
        ];

        foreach ($fieldsToCheck as $modelField => $productField) {
            if (isset($this->$modelField) && $this->$modelField !== $this->product->$productField) {
                return false;
            }
        }

        return true;
    }

    public function getOutOfSyncFields(): array
    {
        if (!$this->product) {
            return [];
        }

        $outOfSync = [];
        $fieldsToCheck = [
            'product_code' => 'product_code',
            'customer_name' => 'customer_name',
            'model_name' => 'model_name',
            'description' => 'description',
            'dimension' => 'dimension',
            'uom' => 'uom',
        ];

        foreach ($fieldsToCheck as $modelField => $productField) {
            if (isset($this->$modelField) && $this->$modelField !== $this->product->$productField) {
                $outOfSync[$modelField] = [
                    'current' => $this->$modelField,
                    'product' => $this->product->$productField,
                ];
            }
        }

        return $outOfSync;
    }
}