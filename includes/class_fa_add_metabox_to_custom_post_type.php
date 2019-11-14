<?php

if(! class_exists('AddMetaBoxToCustomPostTypes')){
	class AddMetaBoxToCustomPostTypes {
		
		public function __construct(){
			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes_to_custom_post_type' ));
		}

		public function activate(){
			//Función que registra el nuevo Custom Post Type [fapushnotification]
			$this->fa_Add_Custom_Post_Type_Push_Notification();
		}
		
		function add_meta_boxes_to_custom_post_type() {
    	add_meta_box(
    		'_priority', // $id
    		'Prioridad', // $title
    		array($this, 'show_priority_meta_box'), // $callback
    		'fapushnotification', // $screen
    		'normal', // $context
    		'low' // $priority
    	);
		
		add_meta_box(
		'data',
		'Datos',
		array( $this, 'show_data_meta_box'),
		'fapushnotification',
		'normal',
		'low'
		);
    }
    
	
	function show_priority_meta_box($post){
		$priority_meta = get_post_meta($post->ID, "_priority", true);
		?>
		<label>Prioridad :  </label>
		
		<select name="custom_element_grid_class" id="custom_element_grid_class">
		  <option value="normal" <?php selected( $priority_meta, 'normal' ); ?>>normal</option>
		  <option value="high" <?php selected( $priority_meta, 'alta' ); ?>>Alta</option>
		</select>
	
	<?php
	}
	
	function show_data_meta_box($post){
		$data = get_post_meta($post->ID, 'data', true);
		?>
		<div id="data-items">
			<div class="data-item">
				<input style="width: calc(50% - 4px);" type="text" placeholder="Clave" name="data[clave1]">
				<input style="width: calc(50% - 4px);" type="text" placeholder="Valor" name="data[valor1]">
			</div>
		</div>
		<script>
		
		(function(){
			var data_items = document.querySelector('#data-items .data-item input[name="data[clave1]"]');
			data_items.addEventListener("keyup", test, false);
			
			
			
			function test(e){
				console.log(e.target.value);
				if(e.target.value != ""){
					index = document.getElementById('data-items').childElementCount;
					newItem = createNewItem(`data[clave${index + 1}]`, `data[valor${index + 1}]`);
					document.getElementById('data-items').appendChild(newItem);
				}
				else{
						document.getElementById('data-items').removeChild(document.getElementById('data-items').lastChild);
				}
			}
			
			function createNewItem(claveName, valorName){
				var data_item = document.createElement('div');
			data_item.classList.add('data-item');
			
			var clave = document.createElement('input');
			clave.style.width = "calc(50% - 4px)";
			clave.type = "text";
			clave.placeholder = "Clave";
			clave.name = claveName;
			
			clave.addEventListener("keyup", test, false);
			
			var valor = document.createElement('input');
			valor.style.width = "calc(50% - 4px)";
			valor.type = "text";
			valor.placeholder = "Valor";
			valor.name = valorName;
			
			data_item.appendChild(clave);
			data_item.appendChild(valor);
			return data_item;
			}
			
		})();
		</script>
		
		<?php
	}
		
	}
	
	$addMetaBoxToCustomPostType = new AddMetaBoxToCustomPostTypes();
}

//activation
register_activation_hook( __FILE__, [ $addMetaBoxToCustomPostType, 'activate' ] );

$newAddMetaBoxToCustomPostTypes = new AddMetaBoxToCustomPostTypes();
return $newAddMetaBoxToCustomPostTypes;
?>
    

