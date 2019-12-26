<?php

namespace App;


class Import extends XmlImport
{
    /**
     * Get city name
     * @return string
     *
     */
    public function getCity()
    {
        $element = $this->xml->{'Классификатор'}->{'Наименование'};
        $string  = (string) $element;

        return preg_replace('/Классификатор \(([^)]*)\)/i', '$1', $string);
    }

    /**
     * Get product iterator
     * @return \Generator
     *
     */
    public function getProductIterator()
    {
        foreach ($this->xml->{'Каталог'}->{'Товары'}->{'Товар'} as $product) {
            yield $product;
        }
    }

    /**
     * Import product to database
     *
     * @param $productData
     */
    public function importProduct($productData)
    {
        if ($product = Product::where('code', $productData->{'Код'})->first()) {
            $product->name   = $productData->{'Наименование'};
            $product->weight = $productData->{'Вес'};
            $product->usage  = $this->getUsages($productData);
            $product->save();
        } else {
            Product::create([
                'code'   => $productData->{'Код'},
                'name'   => $productData->{'Наименование'},
                'weight' => $productData->{'Вес'},
                'usage'  => $this->getUsages($productData)
            ]);
        }
    }

    protected function getUsages($productData)
    {
        if (!isset($productData->{'Взаимозаменяемости'})) {
            return '';
        }
        $usages = [];
        foreach ($productData->{'Взаимозаменяемости'}->{'Взаимозаменяемость'} as $usage) {
            $usages[] = $usage->{'Марка'}.'-'.$usage->{'Модель'}.'-'.$usage->{'КатегорияТС'};
        }

        return implode('|', $usages);
    }
}
