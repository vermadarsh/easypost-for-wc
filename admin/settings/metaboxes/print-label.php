<?php
/**
 * EasyPost label printing metabox.
 *
 * @version 1.0.0
 * @package Easypost_For_Wc
 * @subpackage Easypost_For_Wc/admin/settings/metaboxes
 */

defined( 'ABSPATH' ) || exit;

$order_id              = $post->ID;
$wc_order              = wc_get_order( $order_id );
$line_items            = $wc_order->get_items();
$carrier               = get_post_meta( $order_id, 'wcep_shipping_carrier', true );
$predefined_packages   = wcep_get_easypost_predefined_packages();
$packages              = ( ! empty( $predefined_packages[ $carrier ] ) ) ? $predefined_packages[ $carrier ] : array();
$postage_labels        = get_post_meta( $order_id, 'wcep_postage_labels', true );
$return_labels         = get_post_meta( $order_id, 'wcep_return_labels', true );
$email_label_btn_class = ( empty( $postage_labels ) && empty( $return_labels ) ) ? 'non-clickable' : '';
?>
<div class="woocommerce_order_items_wrapper wc-order-items-editable">
	<h2><strong><?php esc_html_e( 'EasyPost: Print Shipping Labels', 'wc-easypost' ); ?></strong></h2>
	<table cellpadding="0" cellspacing="0" class="woocommerce_order_items">
		<thead>
			<tr>
				<th class="item sortable" colspan="2" data-sort="string-ins"><?php esc_html_e( 'Item', 'wc-easypost' ); ?></th>
				<th class="item_dimensions"><?php esc_html_e( 'L*W*H', 'wc-easypost' ); ?></th>
				<th class="item_weight sortable" data-sort="int"><?php esc_html_e( 'Weight', 'wc-easypost' ); ?></th>
				<?php /* translators: 1: %s: carrier name */ ?>
				<th class="line_packaging_box"><?php echo esc_html( sprintf( __( 'Predefined Package (%1$s)', 'wc-easypost' ), $carrier ) ); ?></th>
				<th class="wc-order-edit-line-item" width="1%">#</th>
			</tr>
		</thead>
		<tbody class="wcep-line-items-tbody">
			<?php
			if ( ! empty( $line_items ) && is_array( $line_items ) ) {
				foreach ( $line_items as $line_item ) {
					$product_id   = $line_item->get_product_id();
					$variation_id = $line_item->get_variation_id();
					$prod_id      = wcep_product_id( $product_id, $variation_id );
					$product      = $line_item->get_product();

					// Product image.
					$attach_id      = get_post_thumbnail_id( $prod_id );
					$prod_thumbnail = wcep_get_image_source_by_id( $attach_id );

					// Product weight.
					$weight        = number_format( $product->get_weight(), 2 );
					$weight_in_oz  = number_format( wc_get_weight( $weight, 'oz' ), 2 );
					$weight_in_lbs = number_format( wc_get_weight( $weight, 'lbs' ), 2 );

					// Product dimentions.
					$length         = $product->get_length();
					$width          = $product->get_width();
					$height         = $product->get_height();
					$length_in_inch = number_format( wc_get_dimension( $length, 'in' ), 2 );
					$width_in_inch  = number_format( wc_get_dimension( $width, 'in' ), 2 );
					$height_in_inch = number_format( wc_get_dimension( $height, 'in' ), 2 );
					?>
					<tr class="item" data-product_ID="<?php echo esc_attr( $prod_id ); ?>" data-order_item_id="<?php echo esc_attr( $line_item->get_id() ); ?>">
						<td class="thumb">
							<div class="wc-order-item-thumbnail">
								<img width="150" height="150" src="<?php echo esc_url( $prod_thumbnail ); ?>" class="attachment-thumbnail size-thumbnail" alt="" title="" />
							</div>
						</td>
						<td class="name" data-sort-value="<?php echo esc_html( $product->get_title() ); ?>">
							<a href="<?php echo esc_url( get_edit_post_link( $prod_id ) ); ?>" class="wc-order-item-name"><?php echo esc_html( $product->get_title() ); ?></a>
							<div class="wc-order-item-sku"><strong><?php esc_html_e( 'SKU:', 'wc-easypost' ); ?></strong> <?php echo esc_html( $product->get_sku() ); ?></div>
						</td>
						<td class="item_dimensions" width="25%" data-item_length="<?php echo esc_attr( $length_in_inch ); ?>" data-item_width="<?php echo esc_attr( $width_in_inch ); ?>" data-item_height="<?php echo esc_attr( $height_in_inch ); ?>">
							<div class="view">
								<?php echo esc_html( "{$length_in_inch}*{$width_in_inch}*{$height_in_inch}" ); ?>
								<br/><small class="times"><?php esc_html_e( '(in inches)', 'wc-easypost' ); ?></small>
							</div>
						</td>
						<td class="item_weight" width="25%" data-sort-value="<?php echo esc_attr( $weight ); ?>" data-item_weight="<?php echo esc_attr( $weight_in_oz ); ?>">
							<div class="view"><?php echo esc_html( $weight_in_oz ); ?> <small class="times">oz</small></div>
							<div class="view"><?php echo esc_html( $weight_in_lbs ); ?> <small class="times">lbs</small></div>
						</td>
						<td class="line_packaging_box" width="30%">
							<div class="view">
								<select class="wc-enhanced-select">
									<?php
									if ( ! empty( $packages ) && is_array( $packages ) ) {
										foreach ( $packages as $package ) {
											echo wp_kses(
												"<option value='{$package}'>{$package}</option>",
												array(
													'option' => array(
														'value'    => array(),
														'selected' => array(),
													),
												)
											);
										}
									}
									?>
								</select>
							</div>
						</td>
						<td class="wc-order-edit-line-item" width="1%">
							<div class="wc-order-edit-line-item-actions">
								<a class="wcep-edit-line-item" href="#"></a>
							</div>
						</td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
</div>
<div class="wc-order-data-row wc-order-bulk-actions wcep-print-label-actions">
	<p class="add-items">
		<button type="button" class="button button-primary shipping-label"><?php echo esc_html( ( ! empty( $postage_labels ) && is_array( $postage_labels ) ) ? __( 'Regenerate Shipping Label', 'wc-easypost' ) : __( 'Shipping Label', 'wc-easypost' ) ); ?></button>
		<button type="button" class="button button-primary shipping-label is-return"><?php echo esc_html( ( ! empty( $return_labels ) && is_array( $return_labels ) ) ? __( 'Regenerate Return Label', 'wc-easypost' ) : __( 'Return Label', 'wc-easypost' ) ); ?></button>
		<button type="button" class="button button-secondary email-label <?php echo esc_attr( $email_label_btn_class ); ?>"><?php esc_html_e( 'Email Label', 'wc-easypost' ); ?></button>
		<input type="hidden" id="wcep-labels-count" data-postage-labels="<?php echo esc_attr( count( $postage_labels ) ); ?>" data-return-labels="<?php echo esc_attr( count( $return_labels ) ); ?>" />
	</p>
</div>
<div class="wc-order-data-row wcep-labels-preview">
	<div class="wcep-postage-labels">
		<h2><strong><?php esc_html_e( 'Postage Labels', 'wc-easypost' ); ?></strong></h2>
		<div class="labels">
			<?php
			if ( ! empty( $postage_labels ) && is_array( $postage_labels ) ) {
				foreach ( $postage_labels as $label_image ) {
					echo wp_kses_post( wcep_get_label_preview_image_html( $label_image ) );
				}
			} else {
				/* translators: 1: %s: opening p tag, 2: %s: closing p tag */
				echo wp_kses_post( sprintf( __( '%1$sNo postage labels created yet !!%2$s', 'wc-easypost' ), '<p>', '</p>' ) );
			}
			?>
		</div>
	</div>

	<!-- RETURN LABEL -->
	<div class="wcep-return-labels">
		<h2><strong><?php esc_html_e( 'Return Labels', 'wc-easypost' ); ?></strong></h2>
		<div class="labels">
			<?php
			if ( ! empty( $return_labels ) && is_array( $return_labels ) ) {
				foreach ( $return_labels as $label_image ) {
					echo wp_kses_post( wcep_get_label_preview_image_html( $label_image ) );
				}
			} else {
				/* translators: 1: %s: opening p tag, 2: %s: closing p tag */
				echo wp_kses_post( sprintf( __( '%1$sNo return labels created yet !!%2$s', 'wc-easypost' ), '<p>', '</p>' ) );
			}
			?>
		</div>
	</div>
</div>
