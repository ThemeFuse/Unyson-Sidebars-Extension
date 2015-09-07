<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$attr['class'] .= ' fw-ext-sidebars-wrap ';
unset( $attr['name'] );
?>

<div <?php echo fw_attr_to_html( $attr ); ?>>
	<?php echo fw()->backend->option_type( 'hidden' )->render( 'slug',
		array(
			'value' => _FW_Extension_Sidebars_Config::POST_TYPES_PREFIX . '_' . get_post_type()
		),
		array(
			'id_prefix'   => $data['id_prefix'] . $id . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . '][selected][0]',
		) ) ?>
	<?php echo fw()->backend->option_type( 'hidden' )->render( '',
		array(
			'value' => get_the_ID()
		),
		array(
			'id_prefix'   => $data['id_prefix'] . $id . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . '][selected][0][ids]',
		) ) ?>
	<?php echo fw()->backend->option_type( 'hidden' )->render( 'preset',
		array(
			'value' => empty( $data['value']['preset_id'] ) ? '' : $data['value']['preset_id']
		),
		array(
			'id_prefix'   => $data['id_prefix'] . $id . '-',
			'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
		)
	) ?>
	<?php if ( ! empty( $data_positions_options ) ): ?>
		<!--MODE INSERT START-->
		<div class="fw-row fw-ext-sidebars-image-picker-box-<?php echo esc_attr($id) ?>">
			<div class="fw-ext-sidebars-option-label fw-col-sm-4 fw-col-md-3 fw-col-lg-2">
				<div class="fw-inner">
					<label for="fw-select-sidebar-for-<?php echo esc_attr($id) ?>"><?php _e( 'Sidebar', 'fw' ) ?></label>

					<div class="fw-clear"></div>
				</div>
			</div>
			<div class="fw-col-sm-8 fw-col-md-9 fw-col-lg-10">
				<div class="fw-backend-option-fixed-width">
					<?php echo fw()->backend->option_type( 'image-picker' )->render( 'position', $data_positions_options,
						array(
							'value'       => empty( $data['value']['position'] ) ? '' : $data['value']['position'],
							'id_prefix'   => $data['id_prefix'] . $id . '-',
							'name_prefix' => $data['name_prefix'] . '[' . $id . ']',
						)
					); ?>
				</div>
			</div>
			<div class="fw-clear"></div>

			<div
				class="fw-ext-sidebars-desc fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
				<?php _e( 'Choose the position for your sidebar(s)', 'fw' ) ?>
			</div>
		</div>

		<div class="fw-clear"></div>
		<div
			class="placeholders fw-insert-mode fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
			<?php foreach ( $colors as $color ) : ?>
				<div class="fw-ext-sidebars-location empty <?php echo esc_attr($id) ?> <?php echo esc_attr($color); ?>"
				     data-color="<?php echo esc_attr($color); ?>">
					<?php echo fw()->backend->option_type( 'select' )->render(
						$color,
						$image_picker,
						array(
							'value'       => empty( $data['value']['sidebars'][ $color ] ) ? '' : $data['value']['sidebars'][ $color ],
							'id_prefix'   => $data['id_prefix'] . $id . '-',
							'name_prefix' => $data['name_prefix'] . '[' . $id . '][sidebars]',
						)
					); ?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="fw-clear"></div>
		<!--MOD INSERT END-->
	<?php else: ?>
		<!--MOD REPLACE START-->

		<div class="placeholders fw-replace-mode fw-row">
			<div class="fw-ext-sidebars-option-label fw-col-sm-4 fw-col-md-3 fw-col-lg-2">
				<div class="fw-inner">
					<label for="fw-select-sidebar-for-<?php echo esc_attr($id) ?>"><?php _e( 'Sidebar', 'fw' ) ?></label>

					<div class="fw-clear"></div>
				</div>
			</div>
			<input type="hidden" class="fw-sidebars-count" value="<?php echo count( $colors ); ?>">

			<div class="fw-col-sm-8 fw-col-md-9 fw-col-lg-10">
				<?php foreach ( $colors as $sidebar_id => $color ) : ?>
					<div class="fw-ext-sidebars-location <?php echo esc_attr($id) ?> <?php echo esc_attr($color); ?>"
					     data-color="<?php echo esc_attr($color); ?>">
						<?php $short_sidebar_name = strlen( $sidebars[ $sidebar_id ]->get_name() ) > 20 ? mb_substr( $sidebars[ $sidebar_id ]->get_name(), 0, 20 ) . '...' : $sidebars[ $sidebar_id ]->get_name(); ?>
						<small
							class="fw-ext-sidebars-placeholder-title"><?php echo __( sprintf( 'Replace %s with:', $short_sidebar_name ), 'fw' ) ?></small>
						<?php echo fw()->backend->option_type( 'select' )->render(
							$color,
							$image_picker,
							array(
								'value'       => empty( $data['value']['sidebars'][ $color ] ) ? '' : $data['value']['sidebars'][ $color ],
								'id_prefix'   => $data['id_prefix'] . $id . '-',
								'name_prefix' => $data['name_prefix'] . '[' . $id . '][sidebars]',
							) ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="fw-clear"></div>

			<div
				class="fw-ext-sidebars-desc fw-col-sm-8 fw-col-sm-offset-4 fw-col-md-9 fw-col-md-offset-3 fw-col-lg-10 fw-col-lg-offset-2">
				<?php _e( 'Select sidebar you wish to replace.', 'fw' ) ?>
			</div>

		</div>

		<div class="fw-clear"></div>
		<!--MOD REPLACE END-->
	<?php endif; ?>
</div>