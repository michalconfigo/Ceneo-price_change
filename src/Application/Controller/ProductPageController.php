<?php

namespace Ceneo\Application\Controller;

use Ceneo\Domain\Model\Category;
use Ceneo\Domain\Model\WoocommerceCategoryWrapper;
use Ceneo\Domain\Service\OptionService;
use Ceneo\Domain\Service\WoocommerceCategoryService;

class ProductPageController extends Controller {

	private $woocommerceCategoryService;
	private $optionService;

	public function __construct() {
		parent::__construct();
		$this->woocommerceCategoryService = new WoocommerceCategoryService();
		$this->optionService              = new OptionService();
	}

	public function getSuggestedAttributes() {
		$currentWoocommerceProduct = wc_get_product();
		$categories                = $this->woocommerceCategoryService->getWrappedCategoryByWoocommerceCategoryId( $currentWoocommerceProduct->get_category_ids() );

		/**
		 * @var WoocommerceCategoryWrapper $category
		 */
		$notMappedAttributes   = array();
		$woocommerceAttributes = array();
		foreach ( $currentWoocommerceProduct->get_attributes() as $taxonomy => $attribute ) {
			$woocommerceAttributes[] = ( substr( $attribute->get_name(), 0, 3 ) === 'pa_' ) ? wc_attribute_label( $taxonomy ) : $attribute->get_name();
		}
		foreach ( $categories as $category ) {
			if ( $category instanceof WoocommerceCategoryWrapper ) {
				if ( $category->getMappedCeneoCategory() instanceof Category ) {
					$notMappedAttributes = array_merge(
						array_diff(
							array_merge(
								( $category->getMappedCeneoCategory()->getKeyAttributes() ? $category->getMappedCeneoCategory()->getKeyAttributes() : array() ),
								( $category->getMappedCeneoCategory()->getOptionalAttributes() ? $category->getMappedCeneoCategory()->getOptionalAttributes() : array() )
							),
							array_merge(
								$woocommerceAttributes,
								array_values( $category->getMappedCeneoAttributes() )
							)
						),
						$notMappedAttributes
					);
				}
			}
		}

		if ( ! empty( $notMappedAttributes ) ) {
			echo $this->renderer->render(
				'product-page/attributes.html.twig',
				array(
					'not_mapped_attributes' => $notMappedAttributes,
				)
			);
		}
	}

	public function getCeneoSettingsMetaBox() {
		global $post;

		echo $this->renderer->render(
			'product-page/meta-box.html.twig',
			array(
				'nonce'             => wp_create_nonce(),
				'exclude_from_sync' => get_post_meta( $post->ID, '_ceneo_exclude_from_sync', true ),
				'disable_buy_now'   => get_post_meta( $post->ID, '_ceneo_disable_buy_now', true ),
			)
		);

	}

	public function postCeneoSettingsMetaBox( $post_id ) {
		$prefix = '_ceneo_';

		if ( isset( $_POST['ceneo_metabox_nonce'] ) && wp_verify_nonce( sanitize_text_field( $_POST['ceneo_metabox_nonce'] ) ) ) {
			if ( 'product' == sanitize_text_field( $_POST['post_type'] ) && current_user_can( 'edit_product', $post_id ) ) {
				update_post_meta( $post_id, $prefix . 'exclude_from_sync', isset( $_POST['exclude_from_sync'] ) );
				update_post_meta( $post_id, $prefix . 'disable_buy_now', isset( $_POST['disable_buy_now'] ) );
			}
		}
	}

	public function getFinalConfirmationScript() {
		if ( is_wc_endpoint_url( 'order-received' ) ) {
			$order_id = absint( get_query_var( 'order-received' ) );
			if ( get_post_type( $order_id ) == 'shop_order' ) {
				$order = wc_get_order( $order_id );

				$products = array();
				foreach ( $order->get_items() as $itemId => $item ) {
					$products[] = array(
						'id'       => $item->get_data()['product_id'],
						'price'    => number_format( $item->get_data()['subtotal'], '2', '.', '' ),
						'quantity' => $item->get_data()['quantity'],
						'currency' => get_woocommerce_currency(),
					);
				}

				$trustedOpinionsConfiguration = $this->optionService->getTrustedOpinionsConfiguration();

				if (
					$trustedOpinionsConfiguration->getCeneoGUID() &&
					$trustedOpinionsConfiguration->getQuestionnaireExpirationDays()
				) {
					$client_email = '';
					if ( get_post_meta( $order->get_id(), 'zo_agree', true ) == 1 ) {
						$client_email = $order->get_billing_email();
					}
					echo $this->renderer->render(
						'trusted-opinions-script.js.twig',
						array(
							'client_email' => $client_email,
							'order_id'     => $order->get_id(),
							'work_days_to_send_questionnaire' => $trustedOpinionsConfiguration->getQuestionnaireExpirationDays(),
							'amount'       => $order->get_total(),
							'products'     => $products,
							'ceneo_guid'   => $trustedOpinionsConfiguration->getCeneoGUID(),
						)
					);
				}
			}
		}
	}

	public function injectZoCheckbox( $fields ) {
		$fields['zo_agree'] = array(
			'type'     => 'checkbox',
			'label'    => 'Biorę udział w programie Zaufane Opinie',
			'required' => false, // Jeśli checkbox ma być obowiązkowy
			'class'    => array( 'form-row-wide' ),
			'clear'    => true, // Opcjonalnie, aby wyświetlić go na nowej linii
		);

		return $fields;
	}

	public function saveZoCheckbox( $order_id ) {
		if ( isset( $_POST['zo_agree'] ) ) {
			update_post_meta( $order_id, 'zo_agree', 1 );
		} else {
			update_post_meta( $order_id, 'zo_agree', 0 );
		}
	}

	public function injectCeneoScript() {
		$trustedOpinionsConfiguration = $this->optionService->getTrustedOpinionsConfiguration();
		if ( $trustedOpinionsConfiguration->getCeneoGUID() ) {
			echo $this->renderer->render(
				'ceneo-script.js.twig',
				array(
					'ceneo_guid' => $trustedOpinionsConfiguration->getCeneoGUID(),
				)
			);
		}
	}
}
