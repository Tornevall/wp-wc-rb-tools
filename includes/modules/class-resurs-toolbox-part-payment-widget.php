<?php

if (!defined('ABSPATH')) {
    exit;
}

class Tornevall_Resurs_Toolbox_Part_Payment_Widget
{
    private static ?WC_Product $detectedProduct = null;
    private static array $detectedProducts = [];
    private static int $shortcodeProductId = 0;

    public static function init(): void
    {
        $shortcodeName = Tornevall_Resurs_Toolbox_Settings::get_shortcode_name();

        add_shortcode($shortcodeName, [self::class, 'render_shortcode']);

        add_action('wp', [self::class, 'detect_context'], 1);

        if ('1' === Tornevall_Resurs_Toolbox_Settings::get_shortcode_enabled()) {
            add_filter('resursbank_pp_price_data', [self::class, 'filter_price_data'], 10, 1);
            add_action('wp', [self::class, 'disable_default_rendering'], 2);
        }
    }

    public static function detect_context(): void
    {
        self::$detectedProduct = self::resolve_product();
        self::$detectedProducts = self::resolve_products();
    }

    public static function disable_default_rendering(): void
    {
        remove_action(
            'woocommerce_single_product_summary',
            'Resursbank\\Woocommerce\\Modules\\PartPayment\\PartPayment::renderWidget'
        );
    }

    public static function filter_price_data($ppAmount)
    {
        if (!empty($ppAmount)) {
            return $ppAmount;
        }

        $product = self::get_detected_product();

        if ($product instanceof WC_Product) {
            return (float) $product->get_price();
        }

        if (self::is_cart_like_context()) {
            return self::get_cart_total_for_price_widget();
        }

        return $ppAmount;
    }

    public static function render_shortcode(array $atts = []): string
    {
        $atts = shortcode_atts(
            [
                'product_id' => 0,
            ],
            $atts,
            Tornevall_Resurs_Toolbox_Settings::get_shortcode_name()
        );

        self::$shortcodeProductId = max(0, (int) $atts['product_id']);

        try {
            if (!class_exists('\\Resursbank\\Woocommerce\\Modules\\PartPayment\\PartPayment')) {
                return '';
            }

            self::$detectedProduct = self::resolve_product();
            self::$detectedProducts = self::resolve_products();

            if (!self::has_product_context() && !self::is_cart_like_context()) {
                return '';
            }

            ob_start();
            \Resursbank\Woocommerce\Modules\PartPayment\PartPayment::renderWidget();

            return (string) (ob_get_clean() ?: '');
        } catch (Throwable $throwable) {
            return $throwable->getMessage();
        }
    }

    public static function has_product_context(): bool
    {
        return self::get_detected_product() instanceof WC_Product || !empty(self::get_detected_products());
    }

    public static function get_detected_product(): ?WC_Product
    {
        if (self::$detectedProduct instanceof WC_Product) {
            return self::$detectedProduct;
        }

        self::$detectedProduct = self::resolve_product();

        return self::$detectedProduct;
    }

    public static function get_detected_products(): array
    {
        if (!empty(self::$detectedProducts)) {
            return self::$detectedProducts;
        }

        self::$detectedProducts = self::resolve_products();

        return self::$detectedProducts;
    }

    private static function resolve_product(): ?WC_Product
    {
        global $product, $post;

        if (self::$shortcodeProductId > 0) {
            $shortcodeProduct = wc_get_product(self::$shortcodeProductId);
            if ($shortcodeProduct instanceof WC_Product) {
                return $shortcodeProduct;
            }
        }

        if ($product instanceof WC_Product) {
            return $product;
        }

        if (function_exists('is_product') && is_product()) {
            $queriedObjectId = get_queried_object_id();
            if (!empty($queriedObjectId)) {
                $queriedProduct = wc_get_product($queriedObjectId);
                if ($queriedProduct instanceof WC_Product) {
                    return $queriedProduct;
                }
            }
        }

        if ($post instanceof WP_Post && $post->post_type === 'product') {
            $postProduct = wc_get_product($post->ID);
            if ($postProduct instanceof WC_Product) {
                return $postProduct;
            }
        }

        $queriedObject = get_queried_object();
        if ($queriedObject instanceof WP_Post && $queriedObject->post_type === 'product') {
            $queriedProduct = wc_get_product($queriedObject->ID);
            if ($queriedProduct instanceof WC_Product) {
                return $queriedProduct;
            }
        }

        if ($post instanceof WP_Post) {
            $productFromBlocks = self::find_product_in_blocks($post);
            if ($productFromBlocks instanceof WC_Product) {
                return $productFromBlocks;
            }
        }

        return null;
    }

    private static function resolve_products(): array
    {
        $products = [];

        $singleProduct = self::resolve_product();
        if ($singleProduct instanceof WC_Product) {
            $products[$singleProduct->get_id()] = $singleProduct;
        }

        if (self::is_cart_like_context() && function_exists('WC') && WC()->cart) {
            foreach (WC()->cart->get_cart() as $cartItem) {
                if (!empty($cartItem['data']) && $cartItem['data'] instanceof WC_Product) {
                    $products[$cartItem['data']->get_id()] = $cartItem['data'];
                    continue;
                }

                if (!empty($cartItem['product_id'])) {
                    $cartProduct = wc_get_product((int) $cartItem['product_id']);
                    if ($cartProduct instanceof WC_Product) {
                        $products[$cartProduct->get_id()] = $cartProduct;
                    }
                }
            }
        }

        return array_values($products);
    }

    private static function find_product_in_blocks(WP_Post $post): ?WC_Product
    {
        if (empty($post->post_content) || !function_exists('parse_blocks')) {
            return null;
        }

        $blocks = parse_blocks($post->post_content);
        $productId = self::find_product_id_in_block_tree($blocks);

        if ($productId > 0) {
            $blockProduct = wc_get_product($productId);
            if ($blockProduct instanceof WC_Product) {
                return $blockProduct;
            }
        }

        return null;
    }

    private static function find_product_id_in_block_tree(array $blocks): int
    {
        foreach ($blocks as $block) {
            $blockName = $block['blockName'] ?? '';
            $attrs = $block['attrs'] ?? [];

            if (
                $blockName === 'woocommerce/single-product' &&
                !empty($attrs['productId'])
            ) {
                return (int) $attrs['productId'];
            }

            if (!empty($attrs['productId']) && self::is_probably_product_block($blockName)) {
                return (int) $attrs['productId'];
            }

            if (!empty($attrs['product_id'])) {
                return (int) $attrs['product_id'];
            }

            if (!empty($attrs['id']) && self::is_probably_product_block($blockName)) {
                return (int) $attrs['id'];
            }

            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $innerProductId = self::find_product_id_in_block_tree($block['innerBlocks']);
                if ($innerProductId > 0) {
                    return $innerProductId;
                }
            }
        }

        return 0;
    }

    private static function is_probably_product_block(string $blockName): bool
    {
        if ($blockName === '') {
            return false;
        }

        if ($blockName === 'woocommerce/single-product') {
            return true;
        }

        if (strpos($blockName, 'woocommerce/') !== 0) {
            return false;
        }

        return
            strpos($blockName, 'product') !== false ||
            strpos($blockName, 'add-to-cart') !== false ||
            strpos($blockName, 'price') !== false ||
            strpos($blockName, 'summary') !== false ||
            strpos($blockName, 'image') !== false ||
            strpos($blockName, 'title') !== false;
    }

    private static function is_cart_like_context(): bool
    {
        return
            (function_exists('is_cart') && is_cart()) ||
            (function_exists('is_checkout') && is_checkout());
    }

    private static function get_cart_total_for_price_widget(): float
    {
        if (!function_exists('WC') || !WC()->cart) {
            return 0.0;
        }

        $total = 0.0;

        foreach (WC()->cart->get_cart() as $cartItem) {
            $lineTotal = isset($cartItem['line_total']) ? (float) $cartItem['line_total'] : 0.0;
            $lineTax = isset($cartItem['line_tax']) ? (float) $cartItem['line_tax'] : 0.0;
            $total += ($lineTotal + $lineTax);
        }

        return $total;
    }
}