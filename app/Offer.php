<?php

namespace App;


use XMLReader;

class Offer extends XmlImport
{
    /**
     * Get city name
     * @return string
     *
     */
    public function getCity()
    {
        while ($this->xml->read()) {
            if ($this->xml->name == 'Наименование' && $this->xml->nodeType == XMLReader::ELEMENT) {
                return preg_replace('/Классификатор \(([^)]*)\)/i', '$1', $this->xml->readString());
            }
        }
    }

    /**
     * Get product iterator
     * @return \Generator
     *
     */
    public function getProductIterator()
    {
        while ($this->xml->read()) {
            if ($this->xml->name == 'Предложение' && $this->xml->nodeType == XMLReader::ELEMENT) {
                break;
            }
        }
        do {
            yield $this->xml->readOuterXml();
        } while ($this->xml->next('Предложение'));
    }

    /**
     * Import offer to database
     *
     * @param $productXmlStr
     */
    public function importProduct($productXmlStr)
    {
        if ($product = Product::where('code', $this->getNodeValue($productXmlStr, 'Код'))->first()) {
            $updateRequired = false;
            $quantity       = $this->getNodeValue($productXmlStr, 'Количество');
            $price          = $this->getNodeValue($productXmlStr, 'ЦенаЗаЕдиницу');
            if ($product->{'quantity_'.$this->getCitySlug()} != $quantity) {
                $product->{'quantity_'.$this->getCitySlug()} = $quantity;
                $updateRequired                              = true;
            }
            if ($product->{'price_'.$this->getCitySlug()} != $price) {
                $product->{'price_'.$this->getCitySlug()} = $price;
                $updateRequired                           = true;
            }
            if ($updateRequired) {
                $product->save();
            }

        } else {
            echo 'Not found: '.$productXmlStr->{'Код'};
        }
    }
}
