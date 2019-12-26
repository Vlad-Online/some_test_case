<?php

namespace App;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class XmlImport
{
    protected $path = '';
    protected $xml;
    protected $citySlug = null;
    protected $columns = [];
    protected $table = 'products';

    /**
     * Import constructor.
     *
     * @param $path string Path to xml file
     */
    public function __construct($path)
    {
        if (file_exists($path)) {
            $this->path = $path;
            $this->xml  = simplexml_load_file($path);
        }
    }

    /**
     * Get city name
     * @return string
     *
     */
    abstract public function getCity();

    /**
     * Get city slug
     * @return string|null
     *
     * @since version
     */
    public function getCitySlug()
    {
        if (!$this->citySlug) {
            $this->citySlug = Str::slug($this->getCity());
        }

        return $this->citySlug;
    }

    /**
     * Get product iterator
     * @return \Generator
     *
     */
    abstract public function getProductIterator();

    /**
     * Import product to database
     *
     * @param $productData
     */
    abstract public function importProduct($productData);

    protected function getTableColumns()
    {
        return Schema::getColumnListing($this->table);
    }

    /**
     * Check required table columns for city slug and create it if required
     *
     * @param $slug string City slug
     *
     * return void
     */
    public function checkColumn($slug)
    {
        if (!count($this->columns)) {
            $this->columns = $this->getTableColumns();
        }

        $quantityColumn = 'quantity_'.$slug;
        $priceColumn    = 'price_'.$slug;

        if (!in_array($quantityColumn, $this->columns)) {
            $this->addQuantityColumn($quantityColumn);
            $this->columns = $this->getTableColumns();
        }

        if (!in_array($priceColumn, $this->columns)) {
            $this->addPriceColumn($priceColumn);
            $this->columns = $this->getTableColumns();
        }
    }

    protected function addQuantityColumn($columnName)
    {
        return Schema::table('products', function (Blueprint $table) use ($columnName) {
            $table->integer($columnName)->default(0);
        });
    }

    protected function addPriceColumn($columnName)
    {
        return Schema::table('products', function (Blueprint $table) use ($columnName) {
            $table->decimal($columnName, 8, 2)->default(0);
        });
    }

}
