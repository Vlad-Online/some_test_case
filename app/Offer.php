<?php

namespace App;


class Offer extends XmlImport
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
        foreach ($this->xml->{'ПакетПредложений'}->{'Предложения'}->{'Предложение'} as $product) {
            yield $product;
        }
    }

    /**
     * Import offer to database
     *
     * @param $productData
     */
    public function importProduct($productData)
    {
        if ($product = Product::where('code', $productData->{'Код'})->first()) {
            $product->{'quantity_'.$this->getCitySlug()} = (string) $productData->{'Количество'};
            $product->{'price_'.$this->getCitySlug()}    = (string) $productData->{'Цены'}->{'Цена'}[0]->{'ЦенаЗаЕдиницу'};
            $product->save();
        } else {
            echo 'Not found: '.$productData->{'Код'};
        }
    }
}
