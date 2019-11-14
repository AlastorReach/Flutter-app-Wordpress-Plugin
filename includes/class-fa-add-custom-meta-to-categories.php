<?php

class AddCustomMetaToCategories {
	
    public function __construct(){
        
    }

    public function init(){
        add_action('category_add_form_fields', array($this,'wcr_category_fields'), 10, 2);
        add_action('created_category', array($this,'wcr_save_category_fields'), 10, 2);
        add_action('category_edit_form_fields', array($this ,'wcr_category_fields'), 10, 2);

        add_action('edited_category', array($this, 'wcr_update_category_fields'), 10, 2);
        
    }
    
    function wcr_category_fields($term) {
    // we check the name of the action because we need to have different output
    // if you have other taxonomy name, replace category with the name of your taxonomy. ex: book_add_form_fields, book_edit_form_fields
    if (current_filter() == 'category_edit_form_fields') {
        $show_in_custom_rest = get_term_meta($term->term_id, 'show_in_custom_rest', true);
        ?>
        <tr class="form-field">
            <th valign="top" scope="row"><label for="term_fields[show_in_custom_rest]">¿Mostrar en la aplicación móvil?</label></th>
            <td>
                <input type="hidden" value="" name="term_fields[show_in_custom_rest]">
                <input type="checkbox" id="show_in_custom_rest" name="term_fields[show_in_custom_rest]" value="yes" <?php echo ( $show_in_custom_rest ) ? checked( $show_in_custom_rest, 'yes' ) : 'no'; ?>"/><br/>
                <span class="description"><?php _e('Selecciona la casilla si deseas que la categoría salga en la aplicación móvil.'); ?></span>
            </td>
        </tr>   
    <?php } elseif (current_filter() == 'category_add_form_fields') {
        ?>
        <div class="form-field">
            <label for="show_in_custom_rest">¿Mostrar en la aplicación móvil?</label>
            <input type="checkbox" value="yes" id="show_in_custom_rest" name="term_fields[show_in_custom_rest]">
            <p class="description"><?php _e('Selecciona la casilla si deseas que la categoría salga en la aplicación móvil.'); ?></p>
        </div>  
    <?php
    }
}

function wcr_save_category_fields($term_id) {
    

    foreach ($_POST['term_fields'] as $key => $value) {
        add_term_meta($term_id, $key, sanitize_text_field($value), true);
    }
}

function wcr_update_category_fields($term_id) {
    if (!isset($_POST['term_fields'])) {
        return;
    }

    foreach ($_POST['term_fields'] as $key => $value) {
        update_term_meta($term_id, $key, sanitize_text_field($value));
    }
}
    
}
$newAddCustomMetaToCategories = new AddCustomMetaToCategories();
$newAddCustomMetaToCategories->init();
return $newAddCustomMetaToCategories;
?>
    

