<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    block_dash
 * @copyright  2019 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace block_dash\data_grid\field;

use block_dash\data_grid\field\attribute\field_attribute_interface;

/**
 * Represents a predefined field that can be added to a data grid.
 *
 * Add basic functionality for field definitions.
 *
 * @package block_dash\data_grid\field
 */
abstract class abstract_field_definition implements field_definition_interface
{
    /**
     * @var string Unique name of field (e.g. u_firstname).
     */
    private $name;

    /**
     * @var string String identifier of human readable name of field (e.g. Firstname).
     */
    private $title;

    /**
     * @var int Visibility of the field (if it should be displayed to the user).
     */
    private $visibility;

    /**
     * @var array Arbitrary options belonging to this field.
     */
    private $options = [];

    /**
     * @var field_attribute_interface[]
     */
    private $attributes = [];

    /**
     * @var bool If field should be sorted.
     */
    private $sort = false;

    /**
     * @var string Direction of sort, if sorting.
     */
    private $sort_direction = 'asc';

    /**
     * @var string Optional sort select (ORDER BY <select>), useful for fields that can't sort based on their field name.
     */
    private $sort_select;

    /**
     * @param string $name String identifier of human readable name of field (e.g. Firstname).
     * @param string $title String identifier of human readable name of field (e.g. Firstname).
     * @param int $visibility Visibility of the field (if it should be displayed to the user).
     * @param array $options Arbitrary options belonging to this field.
     */
    public function __construct($name, $title, $visibility = self::VISIBILITY_VISIBLE, $options = [])
    {
        $this->name = $name;
        $this->title = $title;
        $this->visibility = $visibility;
        $this->options = $options;
    }

    /**
     * After records are relieved from database each field has a chance to transform the data.
     * Example: Convert unix timestamp into a human readable date format
     *
     * @param mixed $data Raw data associated with this field definition.
     * @param \stdClass $record Full record from database.
     * @return mixed
     */
    public final function transform_data($data, \stdClass $record)
    {
        foreach ($this->attributes as $attribute) {
            $data = $attribute->transform_data($data, $record);
        }

        return $data;
    }

    #region Property methods

    /**
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function get_title()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function get_visibility()
    {
        return $this->visibility;
    }

    /**
     * @param int $visibility
     */
    public function set_visibility($visibility)
    {
        // Warn the developer if they have used an invalid visibility.
        // @codeCoverageIgnoreStart
        if (!in_array($visibility, [self::VISIBILITY_HIDDEN, self::VISIBILITY_VISIBLE])) {
            debugging('Invalid visibility set on field ' . get_class($this) . ': ' . $visibility, DEBUG_DEVELOPER);
            // So the application doesn't break, default to visible.
            $visibility = self::VISIBILITY_VISIBLE;
        }
        // @codeCoverageIgnoreEnd
        $this->visibility = $visibility;
    }

    #endregion

    #region Attributes

    /**
     * Add attribute to this field definition.
     *
     * @param field_attribute_interface $attribute
     */
    public function add_attribute(field_attribute_interface $attribute)
    {
        $this->attributes[] = $attribute;
    }

    /**
     * Remove attribute to this field definition.
     *
     * @param field_attribute_interface $attribute
     */
    public function remove_attribute(field_attribute_interface $attribute)
    {
        foreach ($this->attributes as $key => $_attribute) {
            if ($_attribute === $attribute) {
                unset($this->attributes[$key]);
            }
        }
    }

    /**
     * @return field_attribute_interface[]
     */
    public function get_attributes()
    {
        return array_values($this->attributes);
    }

    /**
     * Check if field has an attribute type.
     *
     * @param string $classname Full class path to attribute
     * @return bool
     */
    public function has_attribute($classname)
    {
        foreach ($this->get_attributes() as $attribute) {
            if (get_class($attribute) == $classname) {
                return true;
            }
        }

        return false;
    }

    #endregion

    #region Options

    /**
     * Get a single option.
     *
     * @param $name
     * @return mixed|null
     */
    public function get_option($name)
    {
        return isset($this->options[$name]) ? $this->options[$name] : null;
    }

    /**
     * Set option on field.
     *
     * @param $name
     * @param $value
     */
    public function set_option($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Set options on field.
     *
     * @param array $options
     */
    public function set_options($options)
    {
        foreach ($options as $name => $value) {
            $this->set_option($name, $value);
        }
    }

    /**
     * Get all options for this field.
     *
     * @return array
     */
    public function get_options()
    {
        return $this->options;
    }

    #endregion

    #region Sorting

    /**
     * Set if field should be sorted.
     *
     * @param bool $sort
     * @throws \Exception
     */
    public function set_sort($sort)
    {
        if (!is_bool($sort)) {
            throw new \Exception('Sort expected to be a bool.');
        }

        $this->sort = $sort;
    }

    /**
     * @return bool
     */
    public function get_sort()
    {
        return $this->sort;
    }

    /**
     * Set direction sort should happen for this field.
     *
     * @param $direction
     * @throws \Exception
     */
    public function set_sort_direction($direction)
    {
        if (!in_array($direction, ['desc', 'asc'])) {
            throw new \Exception('Invalid sort direction: ' . $direction);
        }
        $this->sort_direction = $direction;
    }

    /**
     * @return string
     */
    public function get_sort_direction()
    {
        return $this->sort_direction;
    }

    /**
     * Set optional sort select (ORDER BY <select>), useful for fields that can't sort based on their field name.
     *
     * @param $select
     */
    public function set_sort_select($select)
    {
        $this->sort_select = $select;
    }

    /**
     * Return select for ORDER BY.
     *
     * @return string
     */
    public function get_sort_select()
    {
        if (!is_null($this->sort_select)) {
            return $this->sort_select;
        }

        return $this->get_name();
    }

    #endregion

    public function get_custom_form()
    {
        return '<input type="hidden" name="available_field_definitions[' . $this->get_name()
            . '][enabled]" value="1">';
    }
}
