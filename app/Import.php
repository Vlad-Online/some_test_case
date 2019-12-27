<?php

namespace App;


use XMLReader;

class Import extends XmlImport
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
            if ($this->xml->name == 'Товар' && $this->xml->nodeType == XMLReader::ELEMENT) {
                break;
            }
        }
        do {
            yield $this->xml->readOuterXml();
        } while ($this->xml->next('Товар'));
    }

    /**
     * Import product to database
     *
     * @param $productXmlStr
     */
    public function importProduct($productXmlStr)
    {
        if ($product = Product::where('code', $this->getNodeValue($productXmlStr, 'Код'))->first()) {
            $updateRequired = false;
            $name           = $this->getNodeValue($productXmlStr, 'Наименование');
            $weight         = $this->getNodeValue($productXmlStr, 'Вес');
            $usage          = $this->getUsages($productXmlStr);
            if ($product->name != $name) {
                $product->name  = $name;
                $updateRequired = true;
            }

            if ($product->weight != $weight) {
                $product->weight = $weight;
                $updateRequired  = true;
            }

            if ($product->usage != $usage) {
                $product->usage = $usage;
                $updateRequired = true;
            }

            if ($updateRequired) {
                $product->save();
            }

        } else {
            Product::create([
                'code'   => $this->getNodeValue($productXmlStr, 'Код'),
                'name'   => $this->getNodeValue($productXmlStr, 'Наименование'),
                'weight' => $this->getNodeValue($productXmlStr, 'Вес'),
                'usage'  => $this->getUsages($productXmlStr)
            ]);
        }
    }

    protected function getUsages($productXmlStr)
    {
        $xml = new XMLReader();
        $xml->xml($productXmlStr);
        while ($xml->read()) {
            if ($xml->name == 'Взаимозаменяемость' && $xml->nodeType == XMLReader::ELEMENT) {
                $usages = [];
                do {
                    $usages[] = $this->getNodeValue($xml->readOuterXml(),
                            'Марка').'-'.$this->getNodeValue($xml->readOuterXml(),
                            'Модель').'-'.$this->getNodeValue($xml->readOuterXml(), 'КатегорияТС');
                } while ($xml->next('Взаимозаменяемость'));

                return implode('|', $usages);
            }
        }

        return '';
    }
}
