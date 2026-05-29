<?php
/**
 * Shop Page
 * Luxury gallery — sidebar filter left, cards right, no duplicate brand on hover.
 * Backend PHP logic is unchanged from original.
 */

session_start();
include '../includes/DBConn.php';

$sql = "SELECT * FROM tblClothes WHERE approvalStatus = 'approved'";
$result = $conn->query($sql);
$clothes = array();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $clothes[] = $row;
    }
}

$maleFashionImages = array(
    '../images/charles-etoroma-PpLrGyWo7-Q-unsplash.jpg',
    '../images/daniel-adesina-sIARkv6B7fI-unsplash.jpg',
    '../images/mikhail-pasynkov-_GrR2bX183s-unsplash.jpg'
);

$femaleFashionImages = array(
    '../images/anhelina-osaulenko-ypL-2HbvwNU-unsplash.jpg',
    '../images/parsa-foroughi-Nz93TtvjM5o-unsplash.jpg',
    '../images/stan-diordiev-U_HRcBSGYB0-unsplash.jpg'
);

$unisexFashionImages = array(
    '../images/the-ian-PLU3VxyEzxM-unsplash.jpg'
);

function pickFashionImage($category, $clothingName, $clothingID, $maleFashionImages, $femaleFashionImages, $unisexFashionImages) {
    $text        = strtolower(trim($category . ' ' . $clothingName));
    $femaleHints = array('dress', 'skirt', 'women', 'woman', 'ladies', 'blouse');
    $maleHints   = array('men', 'man', 'mens', 'hoodie', 'cargo', 'jacket', 'coat', 'sweater', 'jeans', 'boots');
    $unisexHints = array('unisex', 't-shirt', 'tee', 'shirt', 'shorts', 'classic');

    foreach ($femaleHints as $hint) {
        if (strpos($text, $hint) !== false)
            return $femaleFashionImages[$clothingID % count($femaleFashionImages)];
    }
    foreach ($maleHints as $hint) {
        if (strpos($text, $hint) !== false)
            return $maleFashionImages[$clothingID % count($maleFashionImages)];
    }
    foreach ($unisexHints as $hint) {
        if (strpos($text, $hint) !== false)
            return $unisexFashionImages[$clothingID % count($unisexFashionImages)];
    }

    if ($clothingID % 2 === 0)
        return $maleFashionImages[$clothingID % count($maleFashionImages)];

    return $femaleFashionImages[$clothingID % count($femaleFashionImages)];
}

$conn->close();

/* Featured curated items — names and prices match index.php */
$featuredItems = array(
    array(
        'image'       => '../images/dolce.jpg',
        'title'       => 'Portofino sneakers in calfskin and patent leather',
        'brand'       => 'Dolce & Gabbana',
        'filter'      => 'dolce',
        'price'       => 'R18,500',
        'description' => 'Premium Dolce & Gabbana statement piece. Authentic designer fashion for the discerning shopper.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/dsquared.png',
        'title'       => 'Dsquared2 Denim Jean',
        'brand'       => 'Dsquared2',
        'filter'      => 'dsquared2',
        'price'       => 'R16,000',
        'description' => 'Iconic Dsquared2 denim jean. Contemporary designer style with authentic craftsmanship.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/kenzo.jpg',
        'title'       => 'Kenzo Graphic Tee',
        'brand'       => 'Kenzo',
        'filter'      => 'kenzo',
        'price'       => 'R3,500',
        'description' => 'Signature Kenzo graphic tee. Bold design meets comfort in this premium pre-loved piece.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/lacostejacket.png',
        'title'       => 'Lacoste Monogram Jacket',
        'brand'       => 'Lacoste',
        'filter'      => 'lacoste',
        'price'       => 'R3,000',
        'description' => 'Classic Lacoste jacket. Timeless elegance and quality construction in this iconic piece.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/louboutin.jpg',
        'title'       => 'Louboutin Sneaker',
        'brand'       => 'Christian Louboutin',
        'filter'      => 'louboutin',
        'price'       => 'R23,000',
        'description' => 'Luxury Christian Louboutin sneaker. Premium footwear with iconic design and superior craftsmanship.',
        'link'        => 'shop.php'
    ),
    array(
        'image'       => '../images/LVSneaker.png',
        'title'       => 'Louis Vuitton Trainers',
        'brand'       => 'Louis Vuitton',
        'filter'      => 'lv',
        'price'       => 'R30,000',
        'description' => 'Prestigious Louis Vuitton sneaker. Premium luxury footwear with exceptional quality and design.',
        'link'        => 'shop.php'
    ),
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop — Pastimes</title>
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        /* Dark body for the shop page */
        body.shop-page-body {
            background: #050505;
            color: #F6F7F8;
        }

        body.shop-page-body main {
            background: transparent;
            border-radius: 0;
            margin: 0;
            padding: 0;
            flex: 1;
        }

        body.shop-page-body main > .container {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0;
            box-shadow: none;
            max-width: 100%;
            width: 100%;
        }

        body.shop-page-body footer {
            background: #050505;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }


        /* Page hero — title, subtitle, badge */
        .shop-hero {
            width: 100%;
            padding: 90px 60px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .shop-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            width: 600px;
            height: 300px;
            background: radial-gradient(ellipse at center, rgba(255, 51, 102, 0.07) 0%, transparent 70%);
            pointer-events: none;
        }

        .shop-hero-badge {
            display: inline-block;
            font-size: 0.70rem;
            font-weight: 500;
            letter-spacing: 0.22em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.50);
            border: 1px solid rgba(255, 255, 255, 0.10);
            background: rgba(255, 255, 255, 0.03);
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 24px;
        }

        .shop-hero-title {
            font-size: clamp(2.2rem, 4.5vw, 3.8rem);
            font-weight: 700;
            color: #F6F7F8;
            letter-spacing: -0.02em;
            line-height: 1.1;
            margin: 0 0 16px;
        }

        .shop-hero-title span {
            font-weight: 300;
            color: rgba(246, 247, 248, 0.55);
        }

        .shop-hero-subtitle {
            font-size: 0.95rem;
            font-weight: 300;
            color: rgba(246, 247, 248, 0.50);
            max-width: 440px;
            margin: 0 auto;
            line-height: 1.65;
        }


        /* Main shop body — sidebar left, content right */
        .shop-body {
            display: flex;
            align-items: flex-start;
            gap: 0;
            width: 100%;
            min-height: 80vh;
        }


        /* Left sidebar — search, filter pills, brand filters */
        .shop-sidebar {
            width: 260px;
            flex-shrink: 0;
            position: sticky;
            top: 100px;
            padding: 40px 32px 40px 40px;
            border-right: 1px solid rgba(255, 255, 255, 0.07);
            min-height: 80vh;
        }

        .sidebar-section {
            margin-bottom: 36px;
        }

        .sidebar-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.35);
            margin-bottom: 14px;
            display: block;
        }

        /* Search input */
        .sidebar-search-wrap {
            position: relative;
        }

        .sidebar-search-icon {
            position: absolute;
            left: 13px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(246, 247, 248, 0.30);
            pointer-events: none;
        }

        .sidebar-search {
            width: 100%;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 10px;
            padding: 10px 14px 10px 38px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.80rem;
            color: #F6F7F8;
            outline: none;
            transition: border-color 0.25s ease, background 0.25s ease;
        }

        .sidebar-search::placeholder {
            color: rgba(246, 247, 248, 0.28);
        }

        .sidebar-search:focus {
            border-color: rgba(255, 255, 255, 0.20);
            background: rgba(255, 255, 255, 0.06);
        }

        /* Brand filter pills in the sidebar */
        .sidebar-pills {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-pill {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.80rem;
            font-weight: 400;
            color: rgba(246, 247, 248, 0.50);
            background: transparent;
            border: 1px solid transparent;
            border-radius: 8px;
            padding: 9px 12px;
            cursor: pointer;
            transition: all 0.22s ease;
            text-align: left;
            width: 100%;
        }

        .sidebar-pill:hover {
            color: #F6F7F8;
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.09);
        }

        .sidebar-pill.active {
            color: #F6F7F8;
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .sidebar-pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.20);
            flex-shrink: 0;
            transition: background 0.22s ease;
        }

        .sidebar-pill.active .sidebar-pill-dot {
            background: #FF3366;
        }

        /* Price range in sidebar */
        .sidebar-price-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-price-input {
            flex: 1;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 8px;
            padding: 9px 10px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.78rem;
            color: rgba(246, 247, 248, 0.70);
            outline: none;
            transition: border-color 0.22s ease;
            width: 0;
        }

        .sidebar-price-input::placeholder {
            color: rgba(246, 247, 248, 0.28);
        }

        .sidebar-price-input:focus {
            border-color: rgba(255, 255, 255, 0.20);
        }

        .sidebar-price-sep {
            color: rgba(246, 247, 248, 0.25);
            font-size: 0.80rem;
            flex-shrink: 0;
        }

        /* Sidebar divider line */
        .sidebar-divider {
            width: 100%;
            height: 1px;
            background: rgba(255, 255, 255, 0.06);
            margin-bottom: 36px;
        }


        /* Right content panel — gallery + all listings */
        .shop-content {
            flex: 1;
            min-width: 0;
            padding: 40px 40px 80px 40px;
        }

        /* Section heading above the gallery */
        .content-section-title {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.35);
            margin-bottom: 20px;
            display: block;
        }


        /* Gallery grid — 3 columns on the right panel */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 60px;
        }

        /* Individual gallery card */
        .gallery-card {
            position: relative;
            border-radius: 14px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            cursor: pointer;
            aspect-ratio: 3 / 4;
            opacity: 0;
            transform: translateY(24px);
            transition: transform 0.40s ease, box-shadow 0.40s ease, border-color 0.40s ease;
        }

        .gallery-card.visible {
            animation: cardReveal 0.55s ease forwards;
        }

        @keyframes cardReveal {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .gallery-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.55);
            border-color: rgba(255, 255, 255, 0.14);
        }

        /* Card image */
        .gallery-card-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.55s ease;
            display: block;
        }

        .gallery-card:hover .gallery-card-img {
            transform: scale(1.07);
        }

        /* Dark gradient overlay — only visible on hover */
        .gallery-card-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(5, 5, 5, 0.90) 0%,
                rgba(5, 5, 5, 0.50) 45%,
                transparent 75%
            );
            opacity: 0;
            transition: opacity 0.40s ease;
            z-index: 2;
        }

        .gallery-card:hover .gallery-card-overlay {
            opacity: 1;
        }

        /*
         * Hover info block — slides up from the bottom.
         * Only the product name, price and view button.
         * Brand is NOT shown here to avoid the duplicate.
         */
        .gallery-card-info {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            z-index: 4;
            transform: translateY(16px);
            opacity: 0;
            transition: transform 0.40s ease, opacity 0.40s ease;
        }

        .gallery-card:hover .gallery-card-info {
            transform: translateY(0);
            opacity: 1;
        }

        .gallery-card-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: #F6F7F8;
            margin: 0 0 4px;
            line-height: 1.3;
        }

        .gallery-card-price {
            font-size: 0.82rem;
            font-weight: 500;
            color: rgba(246, 247, 248, 0.75);
            margin: 0 0 14px;
        }

        /* View button inside the card */
        .gallery-card-view {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 0.10em;
            text-transform: uppercase;
            color: #F6F7F8;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.20);
            backdrop-filter: blur(8px);
            padding: 8px 16px;
            border-radius: 999px;
            cursor: pointer;
            transition: background 0.22s ease, border-color 0.22s ease;
        }

        .gallery-card-view:hover {
            background: rgba(255, 255, 255, 0.18);
            border-color: rgba(255, 255, 255, 0.35);
        }

        .gallery-card-arrow {
            transition: transform 0.22s ease;
        }

        .gallery-card-view:hover .gallery-card-arrow {
            transform: translateX(3px);
        }

        /*
         * Always-visible brand tag at the very bottom of the card.
         * This disappears on hover (opacity goes to 0) so there is
         * never a situation where brand shows twice.
         */
        .gallery-card-brand-tag {
            position: absolute;
            bottom: 16px;
            left: 16px;
            right: 16px;
            z-index: 3;
            font-size: 0.65rem;
            font-weight: 500;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.60);
            background: linear-gradient(to top, rgba(5,5,5,0.70) 0%, transparent 100%);
            padding: 20px 4px 0;
            transition: opacity 0.30s ease;
        }

        /* Hide the static brand tag when hovering so it doesn't show alongside the info block */
        .gallery-card:hover .gallery-card-brand-tag {
            opacity: 0;
        }


        /* All Listings section below the gallery */
        .all-listings-title {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.35);
            margin-bottom: 20px;
            display: block;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* DB listings grid */
        .db-products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .db-product-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.07);
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.30s ease, border-color 0.30s ease, box-shadow 0.30s ease;
            display: flex;
            flex-direction: column;
        }

        .db-product-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.13);
            box-shadow: 0 14px 36px rgba(0, 0, 0, 0.40);
        }

        .db-product-img-wrap {
            width: 100%;
            aspect-ratio: 4 / 5;
            overflow: hidden;
            background: #111;
        }

        .db-product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.40s ease;
            filter: saturate(0.85);
        }

        .db-product-card:hover .db-product-img {
            transform: scale(1.05);
        }

        .db-product-body {
            padding: 14px 16px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .db-product-name {
            font-size: 0.88rem;
            font-weight: 500;
            color: #F6F7F8;
            margin: 0 0 4px;
            line-height: 1.4;
        }

        .db-product-price {
            font-size: 0.82rem;
            font-weight: 400;
            color: rgba(246, 247, 248, 0.50);
            margin: 0 0 12px;
        }

        .db-product-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 9px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.11);
            border-radius: 7px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.70rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #F6F7F8;
            text-decoration: none;
            transition: background 0.22s ease, border-color 0.22s ease;
            margin-top: auto;
        }

        .db-product-btn:hover {
            background: rgba(255, 255, 255, 0.11);
            border-color: rgba(255, 255, 255, 0.22);
            color: #F6F7F8;
        }

        .db-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 50px 20px;
            color: rgba(246, 247, 248, 0.30);
            font-size: 0.90rem;
        }


        /* Lightbox overlay */
        .lb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 9000;
            opacity: 0;
            align-items: center;
            justify-content: center;
            transition: opacity 0.35s ease;
        }

        .lb-overlay.open    { display: flex; }
        .lb-overlay.visible { opacity: 1; }

        /* Lightbox modal */
        .lb-modal {
            position: relative;
            background: rgba(12, 12, 12, 0.96);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 18px;
            width: min(860px, 92vw);
            max-height: 90vh;
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.6);
            transform: scale(0.92);
            opacity: 0;
            transition: transform 0.38s ease, opacity 0.38s ease;
        }

        .lb-overlay.visible .lb-modal {
            transform: scale(1);
            opacity: 1;
        }

        .lb-img-side {
            overflow: hidden;
            background: #0a0a0a;
            min-height: 400px;
        }

        .lb-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .lb-info-side {
            padding: 44px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        .lb-kicker {
            font-size: 0.65rem;
            font-weight: 500;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.35);
            margin-bottom: 14px;
        }

        .lb-title {
            font-size: clamp(1rem, 1.8vw, 1.4rem);
            font-weight: 700;
            color: #F6F7F8;
            line-height: 1.25;
            margin: 0 0 8px;
        }

        .lb-brand {
            font-size: 0.72rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgba(246, 247, 248, 0.40);
            margin: 0 0 16px;
        }

        .lb-price {
            font-size: 1.7rem;
            font-weight: 700;
            color: #F6F7F8;
            margin: 0 0 20px;
            letter-spacing: -0.01em;
        }

        .lb-desc {
            font-size: 0.85rem;
            font-weight: 300;
            color: rgba(246, 247, 248, 0.55);
            line-height: 1.7;
            margin: 0 0 28px;
        }

        .lb-cta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 26px;
            background: #F6F7F8;
            color: #0a0a0a;
            border-radius: 999px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.22s ease, box-shadow 0.22s ease;
            width: fit-content;
        }

        .lb-cta:hover {
            background: #fff;
            box-shadow: 0 8px 24px rgba(246, 247, 248, 0.14);
            color: #0a0a0a;
        }

        /* Previous / Next nav arrows */
        .lb-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 42px;
            height: 42px;
            background: rgba(12, 12, 12, 0.80);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #F6F7F8;
            font-size: 1rem;
            transition: background 0.20s ease, border-color 0.20s ease;
        }

        .lb-nav:hover {
            background: rgba(40, 40, 40, 0.95);
            border-color: rgba(255, 255, 255, 0.24);
        }

        .lb-prev { left: -21px; }
        .lb-next { right: -21px; }

        /* Close button */
        .lb-close {
            position: absolute;
            top: 14px;
            right: 14px;
            z-index: 10;
            width: 34px;
            height: 34px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: rgba(246, 247, 248, 0.65);
            font-size: 1.05rem;
            transition: all 0.20s ease;
        }

        .lb-close:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #F6F7F8;
            transform: rotate(90deg);
        }

        /* Slide counter */
        .lb-counter {
            position: absolute;
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            color: rgba(246, 247, 248, 0.30);
        }


        /* Responsive */
        @media (max-width: 1100px) {
            .gallery-grid      { grid-template-columns: repeat(2, 1fr); }
            .db-products-grid  { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 900px) {
            .shop-sidebar {
                display: none;
            }
            .shop-content {
                padding: 32px 24px 60px;
            }
        }

        @media (max-width: 640px) {
            .shop-hero          { padding: 60px 20px 40px; }
            .gallery-grid       { grid-template-columns: 1fr; }
            .db-products-grid   { grid-template-columns: 1fr; }
            .shop-content       { padding: 24px 16px 50px; }
            .lb-modal           { grid-template-columns: 1fr; max-height: 92vh; }
            .lb-img-side        { min-height: 240px; max-height: 36vh; }
            .lb-info-side       { padding: 24px 20px 28px; }
            .lb-prev            { left: 10px; }
            .lb-next            { right: 10px; }
        }
    </style>
</head>
<body class="shop-page-body">
    <?php include '../includes/navbar.php'; ?>

    <main>
        <div class="container" style="width:100%;max-width:100%;">

            <!-- Page hero -->
            <section class="shop-hero">
                <span class="shop-hero-badge">Shop</span>
                <h1 class="shop-hero-title">Luxury Fashion <span>Marketplace</span></h1>
                <p class="shop-hero-subtitle">Browse premium pre-loved fashion pieces available on Pastimes.</p>
            </section>

            <!-- Sidebar + content layout -->
            <div class="shop-body">

                <!-- Left sidebar: search, brand pills, price range -->
                <aside class="shop-sidebar">

                    <!-- Search -->
                    <div class="sidebar-section">
                        <span class="sidebar-label">Search</span>
                        <div class="sidebar-search-wrap">
                            <svg class="sidebar-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <circle cx="11" cy="11" r="6" stroke="currentColor" stroke-width="2"/>
                                <path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                            <input id="searchInput" class="sidebar-search" type="search" placeholder="Search products…" aria-label="Search products">
                        </div>
                    </div>

                    <div class="sidebar-divider"></div>

                    <!-- Brand filter pills -->
                    <div class="sidebar-section">
                        <span class="sidebar-label">Brand</span>
                        <div class="sidebar-pills" id="galleryFilters">
                            <button class="sidebar-pill active" data-filter="all">
                                <span class="sidebar-pill-dot"></span>All Brands
                            </button>
                            <button class="sidebar-pill" data-filter="dolce">
                                <span class="sidebar-pill-dot"></span>Dolce &amp; Gabbana
                            </button>
                            <button class="sidebar-pill" data-filter="dsquared2">
                                <span class="sidebar-pill-dot"></span>Dsquared2
                            </button>
                            <button class="sidebar-pill" data-filter="kenzo">
                                <span class="sidebar-pill-dot"></span>Kenzo
                            </button>
                            <button class="sidebar-pill" data-filter="lacoste">
                                <span class="sidebar-pill-dot"></span>Lacoste
                            </button>
                            <button class="sidebar-pill" data-filter="louboutin">
                                <span class="sidebar-pill-dot"></span>Louboutin
                            </button>
                            <button class="sidebar-pill" data-filter="lv">
                                <span class="sidebar-pill-dot"></span>Louis Vuitton
                            </button>
                        </div>
                    </div>

                    <div class="sidebar-divider"></div>

                    <!-- Price range -->
                    <div class="sidebar-section">
                        <span class="sidebar-label">Price Range (R)</span>
                        <div class="sidebar-price-row">
                            <input id="minPrice" type="number" class="sidebar-price-input" placeholder="Min">
                            <span class="sidebar-price-sep">to</span>
                            <input id="maxPrice" type="number" class="sidebar-price-input" placeholder="Max">
                        </div>
                    </div>

                    <?php
                    /* Dynamic category filter from DB if categories exist */
                    $dbCategories = array_values(array_unique(array_filter(array_column($clothes, 'category'))));
                    if (!empty($dbCategories)):
                    ?>
                    <div class="sidebar-divider"></div>
                    <div class="sidebar-section">
                        <span class="sidebar-label">Category</span>
                        <div class="sidebar-pills">
                            <button class="sidebar-pill active" id="catAll" data-cat="">
                                <span class="sidebar-pill-dot"></span>All Categories
                            </button>
                            <?php foreach ($dbCategories as $cat): ?>
                            <button class="sidebar-pill" data-cat="<?php echo htmlspecialchars(strtolower($cat)); ?>">
                                <span class="sidebar-pill-dot"></span><?php echo htmlspecialchars($cat); ?>
                            </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                </aside>

                <!-- Right content: featured gallery + all listings -->
                <div class="shop-content">

                    <!-- Featured gallery -->
                    <span class="content-section-title">Featured Pieces</span>

                    <div class="gallery-grid" id="galleryGrid">
                        <?php foreach ($featuredItems as $i => $item): ?>
                        <div class="gallery-card"
                             data-filter="<?php echo htmlspecialchars($item['filter']); ?>"
                             data-index="<?php echo $i; ?>"
                             data-name="<?php echo htmlspecialchars(strtolower($item['title'])); ?>"
                             style="animation-delay: <?php echo ($i * 0.08); ?>s;">

                            <img class="gallery-card-img"
                                 src="<?php echo htmlspecialchars($item['image']); ?>"
                                 alt="<?php echo htmlspecialchars($item['title']); ?>"
                                 loading="lazy">

                            <div class="gallery-card-overlay"></div>

                            <!-- Static brand label — hidden on hover so it never duplicates -->
                            <div class="gallery-card-brand-tag">
                                <?php echo htmlspecialchars($item['brand']); ?>
                            </div>

                            <!-- Hover info: name + price + view button only (no brand) -->
                            <div class="gallery-card-info">
                                <p class="gallery-card-name"><?php echo htmlspecialchars($item['title']); ?></p>
                                <p class="gallery-card-price"><?php echo htmlspecialchars($item['price']); ?></p>
                                <button class="gallery-card-view" onclick="openLightbox(<?php echo $i; ?>)">
                                    View <span class="gallery-card-arrow">&#8594;</span>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- All DB listings -->
                    <?php if (count($clothes) > 0): ?>
                    <span class="all-listings-title">
                        All Listings &nbsp;(<?php echo count($clothes); ?> piece<?php echo count($clothes) !== 1 ? 's' : ''; ?>)
                    </span>

                    <div class="db-products-grid" id="productsGrid">
                        <?php foreach ($clothes as $item): ?>
                            <?php
                            $displayImage = pickFashionImage(
                                $item['category'],
                                $item['clothingName'],
                                intval($item['clothingID']),
                                $maleFashionImages,
                                $femaleFashionImages,
                                $unisexFashionImages
                            );

                            $imageToDisplay = $displayImage;
                            if (!empty($item['imageURL']) && file_exists($item['imageURL'])) {
                                $imageToDisplay = $item['imageURL'];
                            }

                            $dataBrand  = isset($item['brand'])  ? $item['brand']  : '';
                            $dataGender = isset($item['gender']) ? $item['gender'] : '';
                            $dataSale   = isset($item['onSale']) ? ($item['onSale'] ? '1' : '0')
                                        : (isset($item['sale']) ? ($item['sale']   ? '1' : '0') : '');
                            ?>
                            <div class="db-product-card"
                                 data-name="<?php echo htmlspecialchars(strtolower($item['clothingName'])); ?>"
                                 data-category="<?php echo htmlspecialchars(strtolower($item['category'])); ?>"
                                 data-brand="<?php echo htmlspecialchars(strtolower($dataBrand)); ?>"
                                 data-gender="<?php echo htmlspecialchars(strtolower($dataGender)); ?>"
                                 data-sale="<?php echo $dataSale; ?>"
                                 data-price="<?php echo $item['price']; ?>">

                                <div class="db-product-img-wrap">
                                    <img class="db-product-img"
                                         src="<?php echo htmlspecialchars($imageToDisplay); ?>"
                                         alt="<?php echo htmlspecialchars($item['clothingName']); ?>"
                                         loading="lazy">
                                </div>

                                <div class="db-product-body">
                                    <p class="db-product-name"><?php echo htmlspecialchars($item['clothingName']); ?></p>
                                    <p class="db-product-price">R <?php echo number_format($item['price'], 2); ?></p>
                                    <a href="product-details.php?id=<?php echo $item['clothingID']; ?>" class="db-product-btn">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </main>

    <!-- Lightbox -->
    <div class="lb-overlay" id="lbOverlay" role="dialog" aria-modal="true" aria-label="Product lightbox">
        <div class="lb-modal" id="lbModal">

            <button class="lb-close" id="lbClose" aria-label="Close">&times;</button>
            <button class="lb-nav lb-prev" id="lbPrev" aria-label="Previous">&#8592;</button>
            <button class="lb-nav lb-next" id="lbNext" aria-label="Next">&#8594;</button>

            <div class="lb-img-side">
                <img class="lb-img" id="lbImg" src="" alt="">
            </div>

            <div class="lb-info-side">
                <p class="lb-kicker">Featured Piece</p>
                <h2 class="lb-title" id="lbTitle"></h2>
                <p class="lb-brand" id="lbBrand"></p>
                <p class="lb-price" id="lbPrice"></p>
                <p class="lb-desc"  id="lbDesc"></p>
                <a href="#" class="lb-cta" id="lbCta">Browse Collection</a>
            </div>

            <div class="lb-counter" id="lbCounter"></div>
        </div>
    </div>

    <footer>
        <p>&copy; 2026 Pastimes. All rights reserved.</p>
    </footer>

    <script>
    (function () {

        /* Gallery brand filter pills (sidebar) */
        var pills      = document.querySelectorAll('#galleryFilters .sidebar-pill');
        var galCards   = document.querySelectorAll('#galleryGrid .gallery-card');

        pills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                pills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');

                var filter = pill.dataset.filter;
                galCards.forEach(function (card) {
                    card.style.display = (filter === 'all' || card.dataset.filter === filter) ? '' : 'none';
                });
            });
        });

        /* Scroll-triggered card reveal */
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08 });

            galCards.forEach(function (card) { io.observe(card); });
        } else {
            galCards.forEach(function (card) { card.classList.add('visible'); });
        }

        /* Open lightbox when clicking a card (but not the view button — it handles itself) */
        galCards.forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (!e.target.closest('.gallery-card-view')) {
                    openLightbox(parseInt(card.dataset.index, 10));
                }
            });
        });


        /* Lightbox */
        var items     = <?php echo json_encode(array_values($featuredItems)); ?>;
        var current   = 0;
        var overlay   = document.getElementById('lbOverlay');
        var lbImg     = document.getElementById('lbImg');
        var lbTitle   = document.getElementById('lbTitle');
        var lbBrand   = document.getElementById('lbBrand');
        var lbPrice   = document.getElementById('lbPrice');
        var lbDesc    = document.getElementById('lbDesc');
        var lbCta     = document.getElementById('lbCta');
        var lbCounter = document.getElementById('lbCounter');

        function populate(idx) {
            var item = items[idx];
            if (!item) return;
            lbImg.src             = item.image;
            lbImg.alt             = item.title;
            lbTitle.textContent   = item.title;
            lbBrand.textContent   = item.brand;
            lbPrice.textContent   = item.price;
            lbDesc.textContent    = item.description;
            lbCta.href            = item.link;
            lbCounter.textContent = (idx + 1) + ' / ' + items.length;
            current = idx;
        }

        window.openLightbox = function (idx) {
            populate(idx);
            overlay.style.display = 'flex';
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    overlay.classList.add('open', 'visible');
                });
            });
            document.body.style.overflow = 'hidden';
        };

        function closeLightbox() {
            overlay.classList.remove('visible');
            setTimeout(function () {
                overlay.classList.remove('open');
                overlay.style.display = '';
                document.body.style.overflow = '';
            }, 360);
        }

        document.getElementById('lbClose').addEventListener('click', closeLightbox);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeLightbox(); });

        document.getElementById('lbPrev').addEventListener('click', function () {
            populate((current - 1 + items.length) % items.length);
        });
        document.getElementById('lbNext').addEventListener('click', function () {
            populate((current + 1) % items.length);
        });

        document.addEventListener('keydown', function (e) {
            if (!overlay.classList.contains('visible')) return;
            if (e.key === 'Escape')      closeLightbox();
            if (e.key === 'ArrowLeft')   populate((current - 1 + items.length) % items.length);
            if (e.key === 'ArrowRight')  populate((current + 1) % items.length);
        });


        /* Live filter for DB listings — search, category, price */
        var searchInput    = document.getElementById('searchInput');
        var minPrice       = document.getElementById('minPrice');
        var maxPrice       = document.getElementById('maxPrice');
        var dbCards        = Array.from(document.querySelectorAll('#productsGrid .db-product-card'));
        var activeCat      = '';

        /* Category pills (if they exist) */
        var catPills = document.querySelectorAll('[data-cat]');
        catPills.forEach(function (pill) {
            pill.addEventListener('click', function () {
                catPills.forEach(function (p) { p.classList.remove('active'); });
                pill.classList.add('active');
                activeCat = pill.dataset.cat;
                filterDb();
            });
        });

        function filterDb() {
            var q      = searchInput ? searchInput.value.trim().toLowerCase() : '';
            var minVal = minPrice    ? (parseFloat(minPrice.value) || 0)      : 0;
            var maxVal = maxPrice    ? (parseFloat(maxPrice.value) || Infinity) : Infinity;

            dbCards.forEach(function (card) {
                var show = true;

                if (q && card.dataset.name && card.dataset.name.indexOf(q) === -1)
                    show = false;

                if (activeCat && card.dataset.category && card.dataset.category.indexOf(activeCat) === -1)
                    show = false;

                var price = parseFloat(card.dataset.price) || 0;
                if (price < minVal || price > maxVal) show = false;

                card.style.display = show ? '' : 'none';
            });
        }

        /* Search also filters gallery cards by name */
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                var q = searchInput.value.trim().toLowerCase();

                galCards.forEach(function (card) {
                    var name = card.dataset.name || '';
                    card.style.display = (!q || name.indexOf(q) !== -1) ? '' : 'none';
                });

                filterDb();
            });
        }

        if (minPrice) minPrice.addEventListener('input', filterDb);
        if (maxPrice) maxPrice.addEventListener('input', filterDb);


        /* Mobile navbar toggle */
        var navbarToggle = document.getElementById('navbarToggle');
        var navbarLinks  = document.getElementById('navbarLinks');

        if (navbarToggle && navbarLinks) {
            navbarToggle.addEventListener('click', function () {
                navbarToggle.classList.toggle('active');
                navbarLinks.classList.toggle('active');
            });

            navbarLinks.querySelectorAll('a').forEach(function (link) {
                link.addEventListener('click', function () {
                    navbarToggle.classList.remove('active');
                    navbarLinks.classList.remove('active');
                });
            });
        }

    })();
    </script>
</body>
</html>
<?php
/*
This code is the original work of:
ST10441421 - Odirile Masemola
ST10450294 - Ripfumelo Mabasa
All rights reserved.
*/
?>