<?php
    include __DIR__.'/../_templates/sitewide.php';
    include __DIR__.'/../backend/lib/autoload.php';
    require_once __DIR__.'/../backend/config.loader.php';
    $page['title'] = 'Checkout &sdot; elementary';
    $page['scripts'] = '<link rel="stylesheet" type="text/css" media="all" href="styles/store.css">';
    include $template['header'];
    include $template['alert'];

    require_once __DIR__.'/../backend/store.php';
    $cart = store_cart();

    require_once __DIR__.'/../backend/shipment.php';
    $shipment = new Shipment();

    if (count($cart) === 0) {
        $error = new Exception('Trying to checkout with an empty cart');
    } else {
        $error = false;
    }

    if (!$error) {
        if (!isset($_POST['name'])) {
            $error = new Exception('Checkout requires a shipment name');
        }

        if (!isset($_POST['address-line1'])) {
            $error = new Exception('Checkout requires a shipment address line');
        }

        if (!isset($_POST['address-level2'])) {
            $error = new Exception('Checkout requires a shipment address city');
        }

        if (!isset($_POST['address-level1'])) {
            $error = new Exception('Checkout requires a shipment address state');
        }

        if (!isset($_POST['country'])) {
            $error = new Exception('Checkout requires a shipment address country');
        }

        if (!isset($_POST['postal-code'])) {
            $error = new Exception('Checkout requires a shipment address postal code');
        }
    }

    if (!$error) {
        try {
            $shipment->set_name($_POST['name']);
            $shipment->set_line1($_POST['address-line1']);
            $shipment->set_line2($_POST['address-line2']);
            $shipment->set_level2($_POST['address-level2']);
            $shipment->set_level1($_POST['address-level1']);
            $shipment->set_country($_POST['country']);
            $shipment->set_postal($_POST['postal-code']);
        } catch (Exception $e) {
            $error = new Exception('Unable to parse shipment form');
        }
    }

    if (!$error) {
        try {
            $shipment->do_validation();
        } catch (Exception $e) {
            $error = new Exception('Unable to verify shipping address');
        }
    }

    // Time to grab all the weight
    if (!$error) {
        try {
            $weight = 0;

            foreach($cart as $id => $product) {
                if (isset($product['weight'])) {
                    $weight = $weight + $product['weight'];
                } else {
                    throw new Exception();
                }
            }

            $shipment->set_weight($weight);
        } catch (Exception $e) {
            $error = new Exception('Unable to calculate weights for shopping cart');
        }
    }

    if (!$error) {
        try {
            $rate = $shipment->get_rate();
        } catch (Exception $e) {
            $error = new Exception('Unable to get rates for shipment');
        }
    }

    if (!$error) {
        $shippingPrice = $rate->RatedShipment[0]->TotalCharges->MonetaryValue;
?>

<div class="row">
    <h1>Checkout</h1>

    <?php
        $sub_total = 0;
        $index = 0;
        foreach ($cart as $id => $product) {
            $sub_total += ($product['quantity'] * $product['retail_price']);
            $index++;
    ?>

    <div class="row row--list row--small">
        <img src="images/store/<?php echo $product['uid'] ?>-small.png"/>
        <div class="information">
            <h3><?php echo $product['full_name'] ?></h3>
            <h3>$<?php echo $product['retail_price'] ?></h3>
        </div>
        <div>
            <input type="hidden" name="product-<?php echo $index ?>-id" value="<?php echo $id ?>">
            <input type="hidden" name="product-<?php echo $index ?>-price" value="<?php echo $product['retail_price'] ?>">
            <label for="product-<?php echo $index ?>-quantity">Qty:</label>
            <input type="number" min="0" max="<?php echo $product['inventory']['quantity_available'] ?>" step="1" value="<?php echo $product['quantity'] ?>" name="product-<?php echo $index ?>-quantity">
        </div>
        <a href="/store/inventory?id=<?php echo $product['id'] ?>&math=subtract&quantity=<?php echo $product['quantity'] ?>">remove</a>
    </div>

    <?php
        }
    ?>

    <div class="row row--small store-totals">
        <hr>
        <h4>Sub-Total: $<?php echo $sub_total; ?></h4>
        <h4>Shipping: $<?php echo $shippingPrice; ?></h4>
        <hr>
        <h4>Total: $<?php echo $sub_total + $shippingPrice; ?></h4>
    </div>

    <div class="row">
        <h2>Shipping information</h2>

        <div>
            <?php echo $shipment->get_name(); ?>
            <?php echo $shipment->get_line1(); ?>
            <?php echo $shipment->get_line2(); ?>
            <?php echo $shipment->get_level2(); ?> <?php echo $shipment->get_level1(); ?>
            <?php echo $shipment->get_postal(); ?> <?php echo $shipment->get_country(); ?>
        </div>
    </div>

    <a href="#" class="button suggested-action">Place order</a>
</div>

<?php } else { ?>

<div class="row">
    <h3><?php echo $error->getMessage(); ?></h3>
    <a href="/store/">Return to store</a>
</div>

<?php
    }

    include $template['footer'];
?>
