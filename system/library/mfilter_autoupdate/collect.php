<?php namespace mfilter_autoupdate;
/*
 * Editing this file may result in loss of license which will be permanently blocked.
 * 
 * @license Commercial
 * @author info@ocdemo.eu
*/

use Countable,
	ArrayAccess,
	ArrayIterator,
	IteratorAggregate;

class Collect implements ArrayAccess, Countable, IteratorAggregate {
	
    public function __construct( $items = [] ) {
        $this->items = array_map(function( $value ) {
			if( is_array( $value ) ) {
				return self::make( $value );
			}

            return $value;
        }, $items);
    }
	
    public static function make( $items = [] ) {
        return new static( $items );
    }
	
    public function isEmpty() {
        return empty( $this->items );
    }
	
    public function isNotEmpty() {
        return ! $this->isEmpty();
    }
	
    public function get( $key, $default = null ) {
        if( $this->offsetExists( $key ) ) {
            return $this->items[$key];
        }

        return $default;
    }
	
    public function all() {
        return $this->items;
    }
	
    public function only( $attributes ) {
        $results = [];

        foreach( is_array($attributes) ? $attributes : func_get_args() as $attribute ) {
            $results[$attribute] = $this->get( $attribute );
        }

        return $results;
    }
	
    public function has( $key ) {
        $keys = is_array( $key ) ? $key : func_get_args();

        foreach($keys as $value ) {
            if( ! $this->offsetExists( $value ) ) {
                return false;
            }
        }

        return true;
    }
	
	public function implode( $glue ) {
		return implode( $glue, $this->items );
	}
	
    public function toArray() {
		return array_map(function( $value ) {
			if( $value instanceof self ) {
				return $value->toArray();
			}

            return $value;
        }, $this->items);
	}
	
    public function getIterator() {
        return new ArrayIterator( $this->items );
    }
	
	public function __get( $name ) {
		if( isset( $this->items[$name] ) ) {
			return $this->offsetGet( $name );
		}
		
		return null;
	}
	
    public function count() {
        return count($this->items);
    }
	
    public function offsetExists( $key ) {
        return array_key_exists($key, $this->items);
    }
	
	public function offsetGet( $key ) {
		return is_array( $this->items[$key] ) ? self::make( $this->items[$key] ) : $this->items[$key];
	}
	
    public function offsetSet( $key, $value ) {
        if( is_null( $key ) ) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }
	
    public function offsetUnset($key) {
        unset( $this->items[$key] );
    }
	
    public function __toString() {
        return collect( $this->items )->toJson();
    }
}