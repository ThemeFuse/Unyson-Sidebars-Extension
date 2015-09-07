<?php if (!defined('FW')) die('Forbidden'); ?>
<?php $colors = fw()->extensions->get('sidebars')->get_allowed_places() ?>

<div class="placeholders fw-replace-mode-<?php echo esc_attr($id) ?> fw-row">
	<div class="fw-ext-sidebars-option-label fw-col-sm-4 fw-col-md-3 fw-col-lg-2">
		<div class="fw-inner">
			<label for="fw-select-sidebar-for-<?php echo esc_attr($id) ?>"><?php _e('Sidebar','fw') ?></label>
			<div class="fw-clear"></div>
		</div>
	</div>
	<input type="hidden" class="fw-sidebars-count" value="<?php echo esc_attr(count($colors)); ?>">

	<div class="fw-col-sm-8 fw-col-md-9 fw-col-lg-10">
		<?php foreach($colors as $sidebar_id => $color) : ?>
			<div class="fw-ext-sidebars-location empty <?php echo esc_attr($id)?> <?php echo esc_attr($color);?>" data-color="<?php echo esc_attr($color);?>">
				<?php $short_sidebar_name = strlen($sidebars[$sidebar_id]->get_name()) > 20 ? mb_substr($sidebars[$sidebar_id]->get_name(), 0, 20) . '...' : $sidebars[$sidebar_id]->get_name();  ?>
				<small class="fw-ext-sidebars-placeholder-title"><?php echo __(sprintf('Replace %s with:', $short_sidebar_name ), 'fw') ?></small>
				<select class="sidebar-selectize <?php echo esc_attr($id); ?>-select">
					<?php if (isset($sidebars) and is_array($sidebars)) :?>
						<?php foreach($sidebars as $sidebar):?>
							<option value="<?php echo esc_attr($sidebar->get_id()) ?>"><?php echo $sidebar->get_name(); ?></option>
						<?php endforeach;?>
					<?php endif;?>
				</select>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="fw-clear"></div>

	<div class="fw-ext-sidebars-desc fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
		<?php _e('Select sidebar you wish to replace.', 'fw')?>
	</div>

</div>

<div class="fw-clear"></div>

<div id="fw-add-button" class="fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
	<input id="submit-settings-<?php echo esc_attr($id) ?>" type="button" class="button button-primary button-large" value="<?php _e('Add Sidebar','fw')?>" />
	<span class="spinner fw-ext-sidebars-submiting-<?php echo esc_attr($id) ?>"></span>
</div>

<div class="fw-clear"></div>