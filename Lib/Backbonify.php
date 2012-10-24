<?php
/**
 * Because you can't use helpers in controller (or models)
 */
class Backbonify {

/**
 * Convert models from CakePHP arrays to Backbone style
 *
 * @param array $models CakePHP find results
 * @param string $alias Alias to look for
 * @return array Array of models with alias removed
 * @author David Kullmann
 */
	public function backbonify($models = array(), $alias, $options = array()) {
		$converted = array();

		if (!empty($models)) {
			
			$key = key($models);

			if (is_integer($key)) {
				foreach ($models as $model) {
					$converted[] = $this->_extractModel($model, $alias, $options);
				}
			} else {
				$converted = $this->_extractModel($models, $alias, $options);
			}
		}
		
		return $converted;
	}
	
	protected function _extractModel($model = array(), $alias = null, $options = array()) {
		$data = array();
		
		if (!$alias) {
			return $model;
		}
		
		if (!empty($options['contain'])) {
			$data = $model[$alias];
			foreach ($options['contain'] as $key => $value) {
				$alias = $key;
				if (is_numeric($key)) {
					$alias = $value;
				}
				if (isset($model[$alias])) {
					$data[$alias] = $model[$alias];
				}
			}
		} elseif (!empty($options['merge'])) {
			if (is_string($options['merge'])) {
				$mergeModel = $options['merge'];
				$data = array_merge($model[$alias], $model[$mergeModel]);
			} elseif (is_array($options['merge'])) {
				$tmp = array();
				foreach($options['merge'] as $mergeModel) {
					$tmp = array_merge($tmp, $model[$mergeModel]);
				}
				$data = array_merge($model[$alias], $tmp);
			}
		} else {
			$data = $model[$alias];
		}
		
		return $data;
	}
	
}