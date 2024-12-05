<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
$source_type = $this->order->get_meta('_wc_order_attribution_source_type') ?? '';
$translated = true;
$baseUrl = home_url();
// Set up the label based on the source type.
switch ( $source_type ) {
    case 'mobile_app':
        $source = $translated ?
            __( 'Mobile app', 'woocommerce' )
            : 'Mobile App';
        break;
    case 'admin':
        $source = $translated ?
            __( 'Web admin', 'woocommerce' )
            : 'Web Admin';
        break;
    case 'typein':
    case 'referral':
    case 'organic':
        $source = $translated ?
            __( 'Web Page', 'woocommerce' )
            : 'Web Page';
        break;
}

$payment_date = get_post_meta($this->order->get_id(), 'payment_date', true);
$sRetPayTime = '';
if ($payment_date) {
    $sRetPayTime = date('Y-m-d H:i:s', strtotime($payment_date) + 8 * 3600) . '(UTC+08:00)'  ;
}


$cur_status = $this->order->get_status();
$prefix_status = $cur_status;
if (!strstr($prefix_status, 'wc-')) {
    $prefix_status = 'wc-' . $cur_status;
}
$allStatus = wc_get_order_statuses();
$displayStatus = $allStatus[$prefix_status] ?? ucfirst($cur_status);


$allTotals = $this->get_woocommerce_totals();
$cusFeeKeys = array_keys($allTotals);
$cusFeeItems = [];
$showedTotals = [];
foreach ($allTotals as $key => $total) {
    if (strstr($key, 'fee_')) {
        $cusFeeItems[$key] = $total;
    } elseif(!strstr($key, 'subtotal')) {
        $showedTotals[$key] = $total;
    }
}
?>

<?php do_action( 'wpo_wcpdf_before_document', $this->get_type(), $this->order ); ?>

<table class="head container">
	<tr>
		<td>
		<?php
	      echo date('Y-m-d H:i:s');
		?>
		</td>
		<td class="shopright" >
			<?php do_action( 'wpo_wcpdf_before_shop_name', $this->get_type(), $this->order ); ?>
			<div class="logo-name">
                <h3><?php echo $baseUrl ?></h3>
                <img src="https://img.365d4u.com/2024/05/logo_white.png" class="custom-logo" alt="custom365d" decoding="async">
            </div>
			<?php do_action( 'wpo_wcpdf_after_shop_name', $this->get_type(), $this->order ); ?>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_document_label', $this->get_type(), $this->order ); ?>
<div >
    <h1 class="document-type-label">Order #<?php echo  $this->order->get_id(); ?></h1>
</div>

<div  class="headerDesc">
<!--    <span>--><?php //echo $this->order_date(); ?><!-- from Woocommerce --><?php //echo $source ?><!--</span>-->
    <table class="innerTable">
        <?php do_action( 'wpo_wcpdf_before_order_data', $this->get_type(), $this->order ); ?>
        <?php if ( isset( $this->settings['display_number'] ) ) : ?>
            <tr class="invoice-number">
                <th><?php $this->number_title(); ?></th>
                <td><?php $this->number( $this->get_type() ); ?></td>
            </tr>
        <?php endif; ?>
        <tr class="invoice-date">
            <th><?php echo 'Order Status:'; ?></th>
            <td><?php echo $displayStatus; ?></td>
        </tr>
        <tr class="invoice-date">
            <th><?php $this->date_title(); ?></th>
            <td><?php $this->date( $this->get_type() ); ?></td>
        </tr>
        <?php if ( $this->get_payment_method() ) : ?>
            <tr class="payment-method">
                <th><?php _e( 'Payment Method:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                <td><?php $this->payment_method(); ?></td>
            </tr>
            <?php if ( $this->get_payment_method() ) : ?>
                <tr class="transaction-id">
                    <th><?php _e( 'Transaction ID:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                    <td><?php echo $this->order->get_transaction_id(); ?></td>
                </tr>
            <?php endif; ?>
            <?php if ( !empty($sRetPayTime) ) : ?>
                <tr class="pay-time">
                    <th><?php _e( 'Pay Time:', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
                    <td><?php echo $sRetPayTime; ?></td>
                </tr>
            <?php endif; ?>
        <?php endif; ?>

        <?php do_action( 'wpo_wcpdf_after_order_data', $this->get_type(), $this->order ); ?>
    </table>
</div>


<?php do_action( 'wpo_wcpdf_after_document_label', $this->get_type(), $this->order ); ?>

<table class="order-data-addresses">
	<tr>
		<td class="address billing-address">
			 <h3><?php _e( 'Billing Address:', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
			<?php do_action( 'wpo_wcpdf_before_billing_address', $this->get_type(), $this->order ); ?>
			<p><?php $this->billing_address(); ?></p>
			<?php do_action( 'wpo_wcpdf_after_billing_address', $this->get_type(), $this->order ); ?>
			<?php if ( isset( $this->settings['display_email'] ) ) : ?>
				<div class="billing-email"><?php $this->billing_email(); ?></div>
			<?php endif; ?>
			<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
				<div class="billing-phone"><?php $this->billing_phone(); ?></div>
			<?php endif; ?>
		</td>
		<td class="address shipping-address">
			<?php if (true) : ?>
				<h3><?php _e( 'Shipping Address:', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
				<?php do_action( 'wpo_wcpdf_before_shipping_address', $this->get_type(), $this->order ); ?>
				<p><?php $this->shipping_address(); ?></p>
				<?php do_action( 'wpo_wcpdf_after_shipping_address', $this->get_type(), $this->order ); ?>
				<?php if ( isset( $this->settings['display_phone'] ) ) : ?>
					<div class="shipping-phone"><?php $this->shipping_phone(); ?></div>
				<?php endif; ?>
			<?php endif; ?>
		</td>
	</tr>
</table>

<?php do_action( 'wpo_wcpdf_before_order_details', $this->get_type(), $this->order ); ?>

<table class="order-details">
	<thead>
		<tr>
			<th class="product"><?php _e( 'Product', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="quantity"><?php _e( 'Quantity', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
			<th class="price"><?php _e( 'Price', 'woocommerce-pdf-invoices-packing-slips' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $this->get_order_items() as $item_id => $item ) : ?>
			<tr class="<?php echo apply_filters( 'wpo_wcpdf_item_row_class', 'item-'.$item_id, esc_attr( $this->get_type() ), $this->order, $item_id ); ?>">
				<td class="product">
					<span class="item-name"><?php echo $item['name']; ?></span>
					<?php do_action( 'wpo_wcpdf_before_item_meta', $this->get_type(), $item, $this->order ); ?>
					<span class="item-meta"><?php echo $item['meta']; ?></span>
<!--					<dl class="meta">-->
<!--						--><?php //if ( ! empty( $item['sku'] ) ) : ?><!--<dt class="sku">--><?php //_e( 'SKU:', 'woocommerce-pdf-invoices-packing-slips' ); ?><!--</dt><dd class="sku">--><?php //echo esc_attr( $item['sku'] ); ?><!--</dd>--><?php //endif; ?>
<!--						--><?php //if ( ! empty( $item['weight'] ) ) : ?><!--<dt class="weight">--><?php //_e( 'Weight:', 'woocommerce-pdf-invoices-packing-slips' ); ?><!--</dt><dd class="weight">--><?php //echo esc_attr( $item['weight'] ); ?><!----><?php //echo esc_attr( get_option( 'woocommerce_weight_unit' ) ); ?><!--</dd>--><?php //endif; ?>
<!--					</dl>-->
					<?php do_action( 'wpo_wcpdf_after_item_meta', $this->get_type(), $item, $this->order ); ?>
				</td>
				<td class="quantity"><?php echo $item['quantity']; ?></td>
				<td class="price"><?php echo $item['order_price']; ?></td>
			</tr>
		<?php endforeach; ?>
        <?php foreach ( $cusFeeItems as $item_id => $total ) : ?>
            <tr class="wpo_wcpdf_item_row_class'">
                <td class="product">
                    <span class="item-name"><?php echo $total['label']; ?></span>
                </td>
                <td class="quantity"><?php echo '1'; ?></td>
                <td class="price"><?php echo $total['value']; ?></td>
            </tr>
        <?php endforeach; ?>
	</tbody>
	<tfoot>
		<tr class="no-borders">
			<td class="no-borders">
				<?php do_action( 'wpo_wcpdf_before_document_notes', $this->get_type(), $this->order ); ?>
				<div class="document-notes">
					<?php if ( $this->get_document_notes() ) : ?>
						<h3><?php _e( 'Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $this->document_notes(); ?>
					<?php endif; ?>
				</div>
				<?php do_action( 'wpo_wcpdf_after_document_notes', $this->get_type(), $this->order ); ?>
				<?php do_action( 'wpo_wcpdf_before_customer_notes', $this->get_type(), $this->order ); ?>
				<div class="customer-notes">
					<?php if ( $this->get_shipping_notes() ) : ?>
						<h3><?php _e( 'Customer Notes', 'woocommerce-pdf-invoices-packing-slips' ); ?></h3>
						<?php $this->shipping_notes(); ?>
					<?php endif; ?>
				</div>
				<?php do_action( 'wpo_wcpdf_after_customer_notes', $this->get_type(), $this->order ); ?>
			</td>
			<td class="no-borders" colspan="2">
				<table class="totals">
					<tfoot>
						<?php foreach ( $showedTotals as $key => $total ) : ?>
							<tr class="<?php echo esc_attr( $key ); ?>">
								<th class="description"><?php echo $total['label']; ?></th>
								<td class="price"><span class="totals-price"><?php echo $total['value']; ?></span></td>
							</tr>
						<?php endforeach; ?>
					</tfoot>
				</table>
			</td>
		</tr>
	</tfoot>
</table>

<div class="bottom-spacer"></div>

<?php do_action( 'wpo_wcpdf_after_order_details', $this->get_type(), $this->order ); ?>

<?php if ( $this->get_footer() ) : ?>
	<htmlpagefooter name="docFooter"><!-- required for mPDF engine -->
		<div id="footer">
			<!-- hook available: wpo_wcpdf_before_footer -->
			<?php $this->footer(); ?>
			<!-- hook available: wpo_wcpdf_after_footer -->
		</div>
	</htmlpagefooter><!-- required for mPDF engine -->
<?php endif; ?>

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>

<style>
    .headerDesc{
        margin-bottom:40px;
        margin-top:-7px;
    }
    .headerDesc .innerTable th{
        padding-right: 20px;
    }
    .shopright{
        text-align: right;
        padding-right: 0;
    }
    .logo-name img{
        width:60px;
        height: 40px;
        display: inline-block;
    }
    .logo-name h3{
        display: inline-block;
        vertical-align: middle;
        padding-right:10px;
        padding-bottom: 10px;
    }
</style>


