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
            $product->{'quantity_'.$this->getCitySlug()} = $this->getNodeValue($productXmlStr, 'Количество');
            $product->{'price_'.$this->getCitySlug()}    = $this->getNodeValue($productXmlStr, 'ЦенаЗаЕдиницу');
            $product->save();
        } else {
            echo 'Not found: '.$productXmlStr->{'Код'};
        }
    }
}
