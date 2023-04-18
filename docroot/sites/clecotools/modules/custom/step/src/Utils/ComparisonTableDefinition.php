<?php

namespace Drupal\step\Utils;

use Drupal\step\Utils\StringHelper;
use Doctrine\Common\Collections\Expr\Comparison;
use function is_array;

class ComparisonTableDefinition
{

    /**
     * The object.
     * Object that is the subject of the comparison.
     *
     * @var array
     */
    protected $object;

    /**
     * The array of comparison data.
     *
     * @var array
     */
    protected $data;

    /**
     * The column definitions.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Keys that should come first in sorting.
     *
     * @var array
     */
    protected $primaryKeys = [];

    /**
     * Optional offset for column groupings.
     * Use when there is a column displayed that isn't defined here
     * (i.e. dynamic checkbox column)
     *
     * @var int
     */
    protected $columnOffset = 0;

    /**
     * Possible column configurations.
     *
     * @var ComparisonTableConfiguration[]
     */
    protected $configurations = [];

    /**
     * ComparisonTableDefinition constructor.
     *
     * @param array $object
     * @param string $dataKey
     */
    public function __construct(array $object, string $dataKey)
    {
        $this->object = $object;
        $this->data   = $this->getArrayPath($object, $dataKey);
    }

    /**
     * SET COLUMN OFFSET
     * Update the column offset.
     *
     * @param int $offset
     */
    public function setColumnOffset(int $offset)
    {
        $this->columnOffset = $offset;
    }

    /**
     * ADD PRIMARY KEY
     * Add an additional primary key.
     *
     * @param string $key
     */
    public function addPrimaryKey(string $key)
    {
        $this->primaryKeys[] = $key;
        $this->primaryKeys   = array_unique($this->primaryKeys);
    }

    /**
     * ADD COLUMN
     * Add a column definition.
     *
     * @param string $label Label for the column header
     * @param null|string $type Data type to apply for the column
     *
     * @return \Drupal\step\Utils\ComparisonTableColumn
     */
    public function addColumn(string $label, $type = null)
    {
        $label           = t($label);
        $column          = new ComparisonTableColumn($label, $type);
        $this->columns[] = $column;

        return $column;
    }

    /**
     * ADD NUMERIC COLUMN
     * Alias function for adding a column with a numeric type.
     *
     * @param string $label Label for the column header
     *
     * @return \Drupal\step\Utils\ComparisonTableColumn
     */
    public function addNumericColumn(string $label)
    {
        return $this->addColumn($label, 'numeric');
    }

    /**
     * GET COLUMN DEFINITIONS
     * Return all defined columns.
     *
     * @return array
     */
    public function getColumnDefinitions()
    {
        return $this->columns;
    }

    /**
     * GET RAW DATA
     * Return the unprocessed data object from the constructor.
     *
     * @return array
     */
    public function getRawData()
    {
        return $this->data;
    }

    /**
     * GET DATA
     * Process the data into its returnable form.
     *
     * @return array
     */
    public function getData()
    {
        $data = $this->getRawData();

        return array_map([$this, 'getItemData'], $data);
    }

    /**
     * GET ITEM DATA
     * Process an individual data row into its returnable form.
     *
     * @param array $item An individual data row
     *
     * @return array
     */
    protected function getItemData(array $item)
    {
        $row = [];
        foreach ($this->getColumnDefinitions() as $i => $column) {
            $key = $column->getKey();
            if (is_array($key)) {
                $value = [];
                foreach ($key as $units => $unitsKey) {
                    $value[$units] = $this->getArrayPath($item, $unitsKey);
                }
            } else {
                $value = $this->getArrayPath($item, $key);
            }

            if (!is_array($value)) $value = (string) $value;

            $row[$column->getSlug()] = $value;
        }

        return $row;
    }

    protected function getColumnData(ComparisonTableColumn $column)
    {
        $data = $this->getData();

        return self::pluck($data, $column->getSlug());
    }

    protected function columnHasData(ComparisonTableColumn $column)
    {
        $data            = $this->getColumnData($column);
        $columnsWithData = array_filter($data, function ($value) {
            if (is_array($value)) {
                $value = array_filter(array_values($value));
            }

            return $value;
        });

        return count(array_filter($columnsWithData)) > 0;
    }

    /**
     * GET GROUPS
     * Return header groupings based on column indexes and
     * presence of unit values or label objects.
     *
     * @return array
     */
    public function getGroups()
    {
        $groups = [];

        $columns = $this->getColumnDefinitions();
        $columns = array_values(array_filter($columns, function ($column) {
            return $this->columnHasData($column);
        }));

        foreach ($columns as $i => $column) {
            if (is_array($column->getKey()) || $column->getUnits()) {
                $groups[] = [
                    'name'  => $column->getLabel(),
                    'start' => ($i + 1) + $this->columnOffset,
                    'span'  => 1,
                    'key'   => StringHelper::createKey($column->getLabel())
                ];
            }
        }

        return $groups;
    }

    /**
     * GET COLUMNS
     * Process the column definitions into their returnable form.
     *
     * @return array
     */
    public function getColumns()
    {
        $columns = [];
        foreach ($this->getColumnDefinitions() as $definition) {
            $units = $definition->getUnits();

            $column          = [];
            $column['slug']  = $definition->getSlug();
            $column['label'] = $units !== null ? $units : $definition->getLabel();
            $column['type']  = $definition->getType();

            if ($this->columnHasData($definition)) {
                $columns[] = $column;
            }
        }

        return $columns;
    }

    /**
     * REGISTER CONFIGURATION
     * Register a column configuration class.
     *
     * @param ComparisonTableConfiguration|string $configuration
     */
    public function registerConfiguration($configuration)
    {
        if (is_string($configuration)) {
            $configuration = new $configuration($this->object);
        }

        $this->configurations[] = $configuration;
    }

    /**
     * REGISTER CONFIGURATIONS
     * Register an array of configurations at once.
     *
     * @param ComparisonTableConfiguration[] $array
     */
    public function registerConfigurations(array $array)
    {
        foreach ($array as $configuration) {
            $this->registerConfiguration($configuration);
        }
    }

    /**
     * APPLY CONFIGURATIONS
     * Apply all configurations whose apply() method returns true
     *
     * @return void
     */
    public function applyConfigurations()
    {
        foreach ($this->configurations as $configuration) {
            if ($configuration->apply($this->object)) {
                $configuration->configure($this);
            }
        }
    }

    /**
     * GET ARRAY PATH
     * Function to retrieve a nested array value via dot notation.
     * e.g. some.nested.value
     *
     * @param array $data Array to retrieve from
     * @param mixed $key Key path
     * @param null $default Default value to return when path does not exist.
     *
     * @return array|mixed|null
     */
    protected static function getArrayPath(array $data, $key, $default = null)
    {
        if (!is_string($key) || empty($key) || !count($data)) {
            return $default;
        }

        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);

            foreach ($keys as $innerKey) {
                if (!array_key_exists($innerKey, $data)) {
                    return $default;
                }

                $data = $data[$innerKey];
            }

            return $data;
        }

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    /**
     * PLUCK
     * Return an array of a specific keys from each item in the provided array.
     *
     * @param array $data Parent array
     * @param string $key Key or array path in dot notation
     * @param null $default Default value if the provided key is not found in an item
     *
     * @return array
     */
    protected static function pluck(array $data, string $key, $default = null)
    {
        $items = [];
        foreach ($data as $item) {
            $items[] = self::getArrayPath($item, $key, $default);
        }

        return $items;
    }

    /**
     * TO ARRAY
     * Return the processed data, groups, and column data.
     *
     * @return array
     */
    public function toArray()
    {
        $this->applyConfigurations();

        return [
            'data'    => $this->getData(),
            'groups'  => $this->getGroups(),
            'columns' => $this->getColumns()
        ];
    }
}
