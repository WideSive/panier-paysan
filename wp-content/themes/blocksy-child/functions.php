<?php
// Charger les styles du thème parent + enfant et styles custom.
function mon_theme_enfant_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'parent-style' ) );
    
    // Global
    wp_enqueue_style(
        'global-custom',
        get_stylesheet_directory_uri() . '/assets/css/global.css',
        array( 'child-style' ),
        '1.0.0'
    );

        // Cards produits (partout où il y a des produits)
    if (is_product_category() || is_tax('producteur') || is_woocommerce()) {
        wp_enqueue_style(
            'products-cards',
            get_stylesheet_directory_uri() . '/assets/css/products-cards.css',
            array('child-style'),
            '1.0.0'
        );
    }

    // Page catégorie avec groupement par producteur
    if (is_product_category()) {
        wp_enqueue_style(
            'page-categories-style',
            get_stylesheet_directory_uri() . '/assets/css/page-categories.css',
            array('child-style', 'products-cards'),
            '1.0.0'
        );
    }
    
    // Page producteur détaillée
    if (is_tax('producteur')) {
        wp_enqueue_style(
            'page-producteur-style',
            get_stylesheet_directory_uri() . '/assets/css/page-producteur.css',
            array('child-style', 'products-cards'),
            '1.0.0'
        );
    }
    
    // Homepage
    if (is_front_page()) {
        wp_enqueue_style(
            'homepage-style',
            get_stylesheet_directory_uri() . '/assets/css/homepage.css',
            array('child-style'),
            '1.0.1'
        );
    }
    
    // WooCommerce global
    if (is_woocommerce()) {
        wp_enqueue_style(
            'woo-custom',
            get_stylesheet_directory_uri() . '/assets/css/woocommerce.css',
            array('child-style'),
            '1.0.1'
        );
    }
}
add_action( 'wp_enqueue_scripts', 'mon_theme_enfant_enqueue_styles' );

// Image de fallback personnalisée pour les produits.
add_filter( 'woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder' );

function custom_woocommerce_placeholder( $src ) {
	return get_stylesheet_directory_uri() . '/assets/images/product-placeholder.webp';
}

// Galerie d'images pour la taxonomie "producteur" (sans ACF Pro).
function ppc_producteur_gallery_field_add( $taxonomy ) {
	?>
	<div class="form-field term-group">
		<label for="producteur_gallery_ids">Galerie d'images</label>
		<input type="hidden" id="producteur_gallery_ids" name="producteur_gallery_ids" value="" />
		<div id="producteur-gallery-preview" style="margin-top:8px;"></div>
		<p>
			<button type="button" class="button" id="producteur-gallery-add">Ajouter des images</button>
			<button type="button" class="button" id="producteur-gallery-remove" style="display:none;">Supprimer la galerie</button>
		</p>
	</div>
	<?php
}
add_action( 'producteur_add_form_fields', 'ppc_producteur_gallery_field_add', 10, 1 );

function ppc_producteur_gallery_field_edit( $term, $taxonomy ) {
	$ids = get_term_meta( $term->term_id, 'producteur_gallery_ids', true );
	?>
	<tr class="form-field term-group-wrap">
		<th scope="row"><label for="producteur_gallery_ids">Galerie d'images</label></th>
		<td>
			<input type="hidden" id="producteur_gallery_ids" name="producteur_gallery_ids" value="<?php echo esc_attr( $ids ); ?>" />
			<div id="producteur-gallery-preview" style="margin-top:8px;"></div>
			<p>
				<button type="button" class="button" id="producteur-gallery-add">Ajouter / modifier</button>
				<button type="button" class="button" id="producteur-gallery-remove" <?php echo empty( $ids ) ? 'style="display:none;"' : ''; ?>>Supprimer la galerie</button>
			</p>
		</td>
	</tr>
	<?php
}
add_action( 'producteur_edit_form_fields', 'ppc_producteur_gallery_field_edit', 10, 2 );

function ppc_producteur_gallery_save( $term_id ) {
	if ( isset( $_POST['producteur_gallery_ids'] ) ) {
		$ids = sanitize_text_field( wp_unslash( $_POST['producteur_gallery_ids'] ) );
		update_term_meta( $term_id, 'producteur_gallery_ids', $ids );
	}
}
add_action( 'created_producteur', 'ppc_producteur_gallery_save', 10, 1 );
add_action( 'edited_producteur', 'ppc_producteur_gallery_save', 10, 1 );

function ppc_producteur_gallery_admin_assets( $hook ) {
	if ( ! in_array( $hook, array( 'edit-tags.php', 'term.php' ), true ) ) {
		return;
	}
	if ( empty( $_GET['taxonomy'] ) || 'producteur' !== $_GET['taxonomy'] ) {
		return;
	}

	wp_enqueue_media();

	$script = <<<JS
jQuery(function($){
  function renderPreview(ids){
    var \$preview = $('#producteur-gallery-preview');
    var \$remove = $('#producteur-gallery-remove');
    \$preview.empty();
    if (!ids) {
      \$remove.hide();
      return;
    }
    var idArr = ids.split(',').filter(Boolean);
    if (!idArr.length) {
      \$remove.hide();
      return;
    }
    idArr.forEach(function(id){
      wp.media.attachment(id).fetch().then(function(){
        var url = wp.media.attachment(id).get('sizes')?.thumbnail?.url || wp.media.attachment(id).get('url');
        if (url) {
          \$preview.append('<img src="'+url+'" style="width:80px;height:80px;object-fit:cover;margin:0 6px 6px 0;border-radius:4px;" />');
        }
      });
    });
    \$remove.show();
  }

  var initialIds = $('#producteur_gallery_ids').val();
  renderPreview(initialIds);

  $('#producteur-gallery-add').on('click', function(e){
    e.preventDefault();
    var frame = wp.media({
      title: 'Sélectionner des images',
      button: { text: 'Utiliser ces images' },
      multiple: true
    });
    frame.on('select', function(){
      var ids = frame.state().get('selection').map(function(attachment){
        attachment = attachment.toJSON();
        return attachment.id;
      }).join(',');
      $('#producteur_gallery_ids').val(ids);
      renderPreview(ids);
    });
    frame.open();
  });

  $('#producteur-gallery-remove').on('click', function(e){
    e.preventDefault();
    $('#producteur_gallery_ids').val('');
    renderPreview('');
  });
});
JS;

	wp_add_inline_script( 'jquery', $script );
}
add_action( 'admin_enqueue_scripts', 'ppc_producteur_gallery_admin_assets' );

// Slider galerie producteur (front).
function ppc_producteur_gallery_front_assets() {
	if ( ! is_tax( 'producteur' ) ) {
		return;
	}

	$script = <<<JS
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.producteur-galerie').forEach(function(gallery) {
    var slider = gallery.querySelector('.producteur-galerie-slider');
    var prev = gallery.querySelector('.producteur-galerie-nav.prev');
    var next = gallery.querySelector('.producteur-galerie-nav.next');
    if (!slider || !prev || !next) return;

    function getStep() {
      var item = slider.querySelector('.producteur-galerie-item');
      if (!item) return 300;
      var rect = item.getBoundingClientRect();
      return rect.width + 12;
    }

    prev.addEventListener('click', function() {
      if (slider.scrollLeft <= 2) {
        slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
        return;
      }
      slider.scrollBy({ left: -getStep(), behavior: 'smooth' });
    });
    next.addEventListener('click', function() {
      var maxScroll = slider.scrollWidth - slider.clientWidth - 2;
      if (slider.scrollLeft >= maxScroll) {
        slider.scrollTo({ left: 0, behavior: 'smooth' });
        return;
      }
      slider.scrollBy({ left: getStep(), behavior: 'smooth' });
    });
  });
});
JS;

	wp_add_inline_script( 'jquery', $script );
}
add_action( 'wp_enqueue_scripts', 'ppc_producteur_gallery_front_assets', 20 );

// Filtres producteurs (front) sur pages catégories produits.
function ppc_producteur_filters_front_assets() {
	if ( ! is_product_category() ) {
		return;
	}

	wp_enqueue_script( 'jquery' );

	$script = <<<JS
document.addEventListener('DOMContentLoaded', function() {
  var filters = document.getElementById('producteur-filters');
  if (!filters) return;

  var checkboxes = Array.prototype.slice.call(document.querySelectorAll('.producteur-checkbox'));
  var sections = Array.prototype.slice.call(document.querySelectorAll('.producteur-section'));
  var countEl = document.getElementById('products-count');
  var selectAll = document.getElementById('select-all');
  var deselectAll = document.getElementById('deselect-all');

  function update() {
    var active = new Set(checkboxes.filter(function(cb){ return cb.checked; }).map(function(cb){ return cb.value; }));
    sections.forEach(function(section){
      var id = section.getAttribute('data-producteur-id');
      var show = active.has(id);
      section.style.display = show ? '' : 'none';
    });

    if (countEl) {
      var count = 0;
      sections.forEach(function(section){
        if (section.style.display === 'none') return;
        count += section.querySelectorAll('.product').length;
      });
      countEl.textContent = count + ' produits affichés';
    }
  }

  checkboxes.forEach(function(cb){
    cb.addEventListener('change', update);
  });

  if (selectAll) {
    selectAll.addEventListener('click', function(){
      checkboxes.forEach(function(cb){ cb.checked = true; });
      update();
    });
  }

  if (deselectAll) {
    deselectAll.addEventListener('click', function(){
      checkboxes.forEach(function(cb){ cb.checked = false; });
      update();
    });
  }

  update();
});
JS;

	wp_add_inline_script( 'jquery', $script );
}
add_action( 'wp_enqueue_scripts', 'ppc_producteur_filters_front_assets', 30 );
