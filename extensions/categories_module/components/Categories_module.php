<?php if (!defined('BASEPATH')) exit('No direct access allowed');

class Categories_module extends Base_Component
{

	public function index() {
		$this->load->model('Menus_model');                                                        // load the menus model
		$this->load->model('Categories_model');                                                        // load the menus model
		$this->lang->load('categories_module/categories_module');

		if (is_numeric($this->input->get('category_id'))) {
			$data['category_id'] = $this->input->get('category_id');
		} else {
			$data['category_id'] = 0;
		}

		$this->assets->setStyleTag(extension_url('categories_module/assets/stylesheet.css'), 'categories-module-css', '155000');

		$data['categories'] = array();
		$results = $this->Categories_model->getCategories(0);                                        // retrieve all menu categories from getCategories method in Menus model
		foreach (sort_array($results) as $result) {                                                            // loop through menu categories array
			$children_data = array();

			if (!empty($result['child_id'])) {
				$sibling_data = array();

				if (!empty($result['sibling_id'])) {
					$sibling = $this->Categories_model->getCategories($result['child_id']);                            // retrieve all menu categories from getCategories method in Menus model

					foreach ($sibling as $sib) {
						$sibling_data[$sib['category_id']] = array(                                                        // create array of category data to pass to view
							'category_id'   => $sib['category_id'],
							'category_name' => $sib['name'],
							'href'          => site_url('menus?category_id=' . $sib['category_id']),
						);
					}
				}

				$children = $this->Categories_model->getCategories($result['category_id']);                            // retrieve all menu categories from getCategories method in Menus model

				foreach ($children as $child) {
					$children_data[$child['category_id']] = array(                                                        // create array of category data to pass to view
						'category_id'   => $child['category_id'],
						'category_name' => $child['name'],
						'href'          => site_url('menus?category_id=' . $child['category_id']),
						'children'      => $sibling_data,
					);
				}
			}

			$data['categories'][$result['category_id']] = array(                                                        // create array of category data to pass to view
				'category_id'   => $result['category_id'],
				'category_name' => $result['name'],
				'href'          => site_url('menus?category_id=' . $result['category_id']),
				'children'      => $children_data,
			);
		}

		$data['menu_total'] = $this->Menus_model->getCount();
		$mix_it_up = ($data['menu_total'] < 500) ? TRUE : FALSE;
		$data['category_tree'] = $this->categoryTree($data['categories'], $mix_it_up);

		// pass array $data and load view files
		return $this->load->view('categories_module/categories_module', $data, TRUE);
	}

	protected function categoryTree($categories, $mix_it_up, $is_child = FALSE) {
		$category_id = $this->input->get('category_id');

		$url = 'menus';
		if ($location_id = $this->input->get('location_id')) {
			$url .= "?location_id={$location_id}";
		}

		$tree = '<ul class="list-group list-group-responsive">';

		if (!$is_child) {
			$tree .= '<li class="list-group-item"><a class="" href="' . restaurant_url($url) . '"><i class="fa fa-angle-right"></i>&nbsp;&nbsp;' . $this->lang->line('text_show_all') . '</a>';
		}

		if (!empty($categories)) {
			foreach ($categories as $category) {
				$permalink = $this->permalink->getPermalink('category_id=' . $category['category_id']);
				$selector = !empty($permalink['slug']) ? $permalink['slug'] : strtolower(str_replace(' ', '-', str_replace('&', '_', $category['category_name'])));

				if ($mix_it_up) {
					$active = ($category_id == $category['category_id']) ? 'selected' : '';
					$attr = ' class="filter ' . $active . '" data-filter=".' . $selector . '" ';
				} else {
					$active = ($category_id == $category['category_id']) ? 'active' : '';
					$attr = ' class="' . $active . '" href="' . restaurant_url($url . ($location_id ? '&' : '?') . 'category_id=' . $category['category_id']) . '" ';
				}

				if (!empty($category['children'])) {
					$tree .= '<li class="list-group-item"><a' . $attr . '><i class="fa fa-angle-right"></i>&nbsp;&nbsp;' . $category['category_name'] . '</a>';
					$tree .= $this->categoryTree($category['children'], $mix_it_up, TRUE);
					$tree .= '</li>';
				} else {
					$tree .= '<li class="list-group-item"><a' . $attr . '><i class="fa fa-angle-right"></i>&nbsp;&nbsp;' . $category['category_name'] . '</a></li>';
				}
			}
		}

		$tree .= '</ul>';

		return $tree;
	}
}

/* End of file Categories_module.php */
/* Location: ./extensions/categories_module/components/Categories_module.php */