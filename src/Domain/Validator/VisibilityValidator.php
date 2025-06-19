<?php

namespace Ceneo\Domain\Validator;

class VisibilityValidator extends Validator {
    public function validate( \WC_Product $product ): bool {
        $product->get_post_password();
        if($product->is_visible() && $product->is_purchasable() && empty($product->post_password)) {
            return true;
        }
        return false;
    }
}
