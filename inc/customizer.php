<?php
/**
 * Customizer settings. Every theme mod is declared once here; templates read
 * them through ridgeline_mod() so the default always matches the Customizer.
 *
 * @package RIDGELINE
 */

/**
 * Google fonts offered in Typography. The value is the Google Fonts family query.
 */
function ridgeline_font_catalog() {
	return array(
		'Barlow Condensed'  => 'Barlow+Condensed:wght@600;700',
		'Barlow'            => 'Barlow:wght@400;500;600;700',
		'Oswald'            => 'Oswald:wght@400;500;600;700',
		'Inter'             => 'Inter:wght@400;500;600;700',
		'Montserrat'        => 'Montserrat:wght@400;500;600;700',
		'Raleway'           => 'Raleway:wght@400;500;600;700',
		'DM Sans'           => 'DM+Sans:wght@400;500;600;700',
		'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@400;500;600;700',
		'Outfit'            => 'Outfit:wght@400;500;600;700',
		'Roboto'            => 'Roboto:wght@400;500;700',
		'Lora'              => 'Lora:wght@400;500;600;700',
		'Playfair Display'  => 'Playfair+Display:wght@400;600;700',
	);
}

/**
 * Select choices for the font controls.
 */
function ridgeline_font_choices() {
	$choices = array();
	foreach ( array_keys( ridgeline_font_catalog() ) as $name ) {
		$choices[ $name ] = $name;
	}
	return $choices;
}

/**
 * Saved font, restricted to the catalog.
 *
 * @param string $key Theme mod key without the ridgeline_ prefix.
 */
function ridgeline_font_family( $key ) {
	$catalog = ridgeline_font_catalog();
	$name    = (string) ridgeline_mod( $key );
	if ( isset( $catalog[ $name ] ) ) {
		return $name;
	}
	return 'font_heading' === $key ? 'Barlow Condensed' : 'Barlow';
}

/**
 * CSS font stack for a saved font.
 *
 * @param string $key     Theme mod key without the ridgeline_ prefix.
 * @param bool   $heading Whether this is the heading stack.
 */
function ridgeline_font_stack( $key, $heading = false ) {
	$name = '"' . ridgeline_font_family( $key ) . '"';
	return $heading ? $name . ', "Arial Narrow", Arial, sans-serif' : $name . ', "Segoe UI", Arial, sans-serif';
}

/**
 * Setting registry: section id => title, panel, fields.
 */
function ridgeline_settings() {
	static $settings = null;
	if ( null !== $settings ) {
		return $settings;
	}

	$settings = array(
		/* ---------- Site-wide ---------- */
		'dealer'       => array(
			'title'  => 'Dealer Information',
			'panel'  => 'ridgeline_global',
			'fields' => array(
				'dealer_name'    => array( 'label' => 'Business name', 'default' => 'Ridgeline Power Equipment' ),
				'dealer_tagline' => array( 'label' => 'Logo subtitle', 'default' => 'Power Equipment' ),
				'dealer_about'   => array( 'label' => 'Short description (footer)', 'type' => 'textarea', 'default' => 'Family-owned since 1974. We sell and service mowers, tractors, snow blowers, chainsaws, generators and utility vehicles — and we answer the phone.' ),
				'dealer_phone'   => array( 'label' => 'Phone', 'default' => '(717) 555-0142' ),
				'dealer_text'    => array( 'label' => 'Text / SMS number (empty hides it)', 'default' => '(717) 555-0199' ),
				'dealer_email'   => array( 'label' => 'Email', 'type' => 'email', 'default' => 'sales@ridgelinepower.example' ),
				'dealer_street'  => array( 'label' => 'Street address', 'default' => '1840 Quarry Road' ),
				'dealer_city'    => array( 'label' => 'City, state ZIP', 'default' => 'Carlisle, PA 17013' ),
				'dealer_hours'   => array( 'label' => 'Hours (short, header)', 'default' => 'Mon–Fri 8–6 · Sat 8–4' ),
				'dealer_hours_full' => array( 'label' => 'Hours (full)', 'default' => 'Mon–Fri 8–6 · Sat 8–4 · Sun closed' ),
				'dealer_since'   => array( 'label' => 'Founded year', 'default' => '1974' ),
				'social_facebook'  => array( 'label' => 'Facebook URL', 'type' => 'url', 'default' => '' ),
				'social_instagram' => array( 'label' => 'Instagram URL', 'type' => 'url', 'default' => '' ),
				'social_youtube'   => array( 'label' => 'YouTube URL', 'type' => 'url', 'default' => '' ),
			),
		),
		'announcement' => array(
			'title'  => 'Announcement Bar',
			'panel'  => 'ridgeline_global',
			'fields' => array(
				'announce_enabled'     => array( 'label' => 'Show announcement bar', 'type' => 'checkbox', 'default' => true ),
				'announce_text'        => array( 'label' => 'Message', 'default' => 'Fall tune-up special: 15% off service through Oct 31.' ),
				'announce_link_text'   => array( 'label' => 'Link text (optional)', 'default' => 'Book service →' ),
				'announce_link_url'    => array( 'label' => 'Link URL (optional)', 'type' => 'url', 'default' => '/service/' ),
				'announce_dismissible' => array( 'label' => 'Visitors can dismiss it', 'type' => 'checkbox', 'default' => true ),
			),
		),
		'header'       => array(
			'title'  => 'Header',
			'panel'  => 'ridgeline_global',
			'fields' => array(
				'header_contact_bar'  => array( 'label' => 'Show contact bar', 'type' => 'checkbox', 'default' => true ),
				'header_sticky'       => array( 'label' => 'Sticky header', 'type' => 'checkbox', 'default' => true ),
				'header_logo_letter'  => array( 'label' => 'Logo mark letter (used when no logo image)', 'default' => 'R' ),
				'header_search_text'  => array( 'label' => 'Search placeholder', 'default' => 'Search model, SKU, brand or product' ),
				'header_suggestions'  => array( 'label' => 'Show live search suggestions', 'type' => 'checkbox', 'default' => true ),
				'header_popular'      => array( 'label' => 'Popular categories in search (one per line)', 'type' => 'textarea', 'default' => "Zero-Turn Mowers\nSnow Blowers\nChainsaws\nGenerators\nLawn Tractors\nParts & Filters" ),
				'header_account_url'  => array( 'label' => 'Account link (empty uses My Account)', 'type' => 'url', 'default' => '' ),
				'header_account_text' => array( 'label' => 'Account label', 'default' => 'Account' ),
				'header_shop_mega'    => array( 'label' => 'Add a "Shop" mega menu of categories to the navigation', 'type' => 'checkbox', 'default' => true ),
				'header_brands_mega'  => array( 'label' => 'Add a "Brands" mega menu to the navigation', 'type' => 'checkbox', 'default' => true ),
			),
		),
		'colors'       => array(
			'title'  => 'Brand Colors',
			'panel'  => 'ridgeline_global',
			'fields' => array(
				'color_brand'   => array( 'label' => 'Brand', 'type' => 'color', 'default' => '#1E5A3C' ),
				'color_brand_d' => array( 'label' => 'Brand dark', 'type' => 'color', 'default' => '#143D28' ),
				'color_brand_l' => array( 'label' => 'Brand light tint', 'type' => 'color', 'default' => '#E4EFE8' ),
				'color_accent'  => array( 'label' => 'Accent', 'type' => 'color', 'default' => '#F2B100' ),
				'color_ink'     => array( 'label' => 'Ink (text and dark surfaces)', 'type' => 'color', 'default' => '#14201A' ),
			),
		),
		'typography'  => array(
			'title'       => 'Typography',
			'panel'       => 'ridgeline_global',
			'description' => 'Heading and body fonts, plus the sizes used by page titles, section headings and body text.',
			'fields'      => array(
				'font_heading'       => array( 'label' => 'Heading font', 'type' => 'select', 'default' => 'Barlow Condensed', 'choices' => ridgeline_font_choices() ),
				'font_body'          => array( 'label' => 'Body font', 'type' => 'select', 'default' => 'Barlow', 'choices' => ridgeline_font_choices() ),
				'font_size_base'     => array( 'label' => 'Base font size (px)', 'type' => 'number', 'default' => 17, 'input_attrs' => array( 'min' => 13, 'max' => 22, 'step' => 1 ) ),
				'font_size_h1'       => array( 'label' => 'H1 size (px)', 'type' => 'number', 'default' => 56, 'input_attrs' => array( 'min' => 24, 'max' => 96, 'step' => 2 ) ),
				'font_size_h2'       => array( 'label' => 'H2 size (px)', 'type' => 'number', 'default' => 40, 'input_attrs' => array( 'min' => 20, 'max' => 72, 'step' => 2 ) ),
				'font_size_h3'       => array( 'label' => 'H3 size (px)', 'type' => 'number', 'default' => 24, 'input_attrs' => array( 'min' => 16, 'max' => 48, 'step' => 1 ) ),
				'font_size_h4'       => array( 'label' => 'H4 size (px)', 'type' => 'number', 'default' => 22, 'input_attrs' => array( 'min' => 14, 'max' => 36, 'step' => 1 ) ),
				'section_title_size' => array( 'label' => 'Section title size (px)', 'type' => 'number', 'default' => 40, 'input_attrs' => array( 'min' => 20, 'max' => 72, 'step' => 2 ) ),
			),
		),
		'footer'       => array(
			'title'  => 'Footer',
			'panel'  => 'ridgeline_global',
			'fields' => array(
				'footer_newsletter'        => array( 'label' => 'Show newsletter', 'type' => 'checkbox', 'default' => true ),
				'footer_newsletter_title'  => array( 'label' => 'Newsletter heading', 'default' => 'Seasonal deals, in your inbox' ),
				'footer_newsletter_text'   => array( 'label' => 'Newsletter text', 'default' => 'One email a month. No spam.' ),
				'footer_newsletter_action' => array( 'label' => 'Newsletter form action URL', 'type' => 'url', 'default' => '' ),
				'footer_map'               => array( 'label' => 'Show map strip', 'type' => 'checkbox', 'default' => true ),
				'footer_credit_url'        => array( 'label' => 'Design credit link', 'type' => 'url', 'default' => 'https://www.targetweb.com/' ),
			),
		),

		/* ---------- Homepage ---------- */
		'home_layout'  => array(
			'title'       => 'Section Order',
			'panel'       => 'ridgeline_home',
			'description' => 'Comma-separated section keys in display order. Remove a key to hide that section. Keys: hero, finder, categories, featured, brands, story, features, trust, promo, testimonials, compare, contact.',
			'fields'      => array(
				'home_order' => array( 'label' => 'Section order', 'default' => 'hero, finder, categories, featured, brands, story, features, trust, promo, testimonials, compare, contact' ),
			),
		),
		'home_hero'    => array(
			'title'  => 'Hero Slider',
			'panel'  => 'ridgeline_home',
			'fields' => ridgeline_hero_fields(),
		),
		'home_finder'  => array(
			'title'  => 'Equipment Finder',
			'panel'  => 'ridgeline_home',
			'fields' => array(
				'finder_heading'   => array( 'label' => 'Heading', 'default' => 'Find your equipment' ),
				'finder_hint'      => array( 'label' => 'Hint (use {count})', 'default' => 'Browse {count} machines by type and brand' ),
				'finder_cat_all'   => array( 'label' => 'Category default option', 'default' => 'All categories' ),
				'finder_brand_all' => array( 'label' => 'Brand default option', 'default' => 'All brands' ),
				'finder_stock'     => array( 'label' => 'In-stock toggle label', 'default' => 'In stock only' ),
				'finder_button'    => array( 'label' => 'Button text', 'default' => 'Show results' ),
			),
		),
		'home_categories' => array(
			'title'  => 'Shop by Category',
			'panel'  => 'ridgeline_home',
			'fields' => array_merge(
				array(
					'cats_heading'  => array( 'label' => 'Heading', 'default' => 'Shop by category' ),
					'cats_link'     => array( 'label' => 'Link text', 'default' => 'All categories →' ),
					'cats_source'   => array(
						'label'       => 'Category cards',
						'description' => 'Automatic uses WooCommerce categories. Manual uses the card titles, images and links below.',
						'type'        => 'select',
						'default'     => 'automatic',
						'choices'     => array( 'automatic' => 'Automatic categories', 'manual' => 'Manual cards' ),
					),
					'cats_limit'    => array( 'label' => 'Number of automatic categories', 'type' => 'number', 'default' => 8 ),
					'cats_carousel' => array( 'label' => 'Show as a carousel', 'type' => 'checkbox', 'default' => false ),
				),
				ridgeline_manual_category_fields()
			),
		),
		'home_featured' => array(
			'title'  => 'Featured Products',
			'panel'  => 'ridgeline_home',
			'fields' => array(
				'featured_heading' => array( 'label' => 'Heading', 'default' => 'Featured equipment' ),
				'featured_link'    => array( 'label' => 'Link text', 'default' => 'Shop all →' ),
				'featured_source'  => array( 'label' => 'Products', 'type' => 'select', 'default' => 'newest', 'choices' => array( 'newest' => 'Newest', 'featured' => 'Featured', 'sale' => 'On sale', 'best' => 'Best selling', 'category' => 'From a category' ) ),
				'featured_cat'     => array( 'label' => 'Category slug (when "From a category")', 'default' => '' ),
				'featured_count'   => array( 'label' => 'Number of products', 'type' => 'number', 'default' => 4 ),
			),
		),
		'home_brands'  => array(
			'title'  => 'Shop by Brand',
			'panel'  => 'ridgeline_home',
			'fields' => array(
				'brands_heading'  => array( 'label' => 'Heading', 'default' => 'Shop by brand' ),
				'brands_carousel' => array( 'label' => 'Show as a carousel', 'type' => 'checkbox', 'default' => false ),
				'brands_opacity'  => array( 'label' => 'Logo opacity (0–100)', 'type' => 'number', 'default' => 100 ),
			),
		),
		'home_story'   => array(
			'title'  => 'Story Blocks',
			'panel'  => 'ridgeline_home',
			'fields' => array(
				'story1_eyebrow' => array( 'label' => 'Block 1 eyebrow', 'default' => 'Our story' ),
				'story1_heading' => array( 'label' => 'Block 1 heading', 'default' => 'Three generations behind the counter.' ),
				'story1_text'    => array( 'label' => 'Block 1 text', 'type' => 'textarea', 'default' => 'Ridgeline opened in 1974 as a two-bay repair shop. Today we stock hundreds of machines, but the idea hasn’t changed: sell what works, fix what breaks, and talk to people like neighbors.' ),
				'story1_button'  => array( 'label' => 'Block 1 button', 'default' => 'About Ridgeline' ),
				'story1_url'     => array( 'label' => 'Block 1 link', 'type' => 'url', 'default' => '/about/' ),
				'story1_image'   => array( 'label' => 'Block 1 image', 'type' => 'image', 'default' => '' ),
				'story2_eyebrow' => array( 'label' => 'Block 2 eyebrow', 'default' => 'Service department' ),
				'story2_heading' => array( 'label' => 'Block 2 heading', 'default' => 'Factory-trained. Parts on the shelf.' ),
				'story2_text'    => array( 'label' => 'Block 2 text', 'type' => 'textarea', 'default' => 'Most repairs are back in your hands within five business days. Pickup and delivery available within 25 miles.' ),
				'story2_button'  => array( 'label' => 'Block 2 button', 'default' => 'Request service' ),
				'story2_url'     => array( 'label' => 'Block 2 link', 'type' => 'url', 'default' => '/service/' ),
				'story2_image'   => array( 'label' => 'Block 2 image', 'type' => 'image', 'default' => '' ),
			),
		),
		'home_features' => array(
			'title'       => 'Feature Blocks',
			'panel'       => 'ridgeline_home',
			'description' => 'One block per line: Heading | Text | Link (optional).',
			'fields'      => array(
				'features_list' => array( 'label' => 'Blocks', 'type' => 'textarea', 'default' => "Buy online, pick up today | Order in minutes and we’ll have it staged and tested at the counter.\nSet up and ready to run | Every machine is assembled, fueled and checked by a technician.\nParts for what you own | Belts, blades, filters and OEM parts for most brands we sell." ),
			),
		),
		'home_trust'   => array(
			'title'       => 'Why Choose Us',
			'panel'       => 'ridgeline_home',
			'description' => 'One item per line: Heading | Text.',
			'fields'      => array(
				'trust_heading' => array( 'label' => 'Heading', 'default' => 'Why Ridgeline' ),
				'trust_list'    => array( 'label' => 'Items', 'type' => 'textarea', 'default' => "Since 1974 | Family-owned, same address, same phone number.\nFactory-trained techs | Certified on every brand we carry.\nReal people on the phone | Call and talk to the counter, not a queue.\nPrice-match promise | We’ll meet any local in-stock price." ),
			),
		),
		'home_promo'   => array(
			'title'  => 'Promo Banner',
			'panel'  => 'ridgeline_home',
			'fields' => array(
				'promo_eyebrow' => array( 'label' => 'Eyebrow', 'default' => 'Limited time' ),
				'promo_heading' => array( 'label' => 'Heading', 'default' => '0% for 36 months on tractors over $2,500.' ),
				'promo_text'    => array( 'label' => 'Text', 'default' => 'Pre-season snow blower pricing starts Nov 1. Reserve yours now.' ),
				'promo_button'  => array( 'label' => 'Button', 'default' => 'See financing' ),
				'promo_url'     => array( 'label' => 'Link', 'type' => 'url', 'default' => '/financing/' ),
				'promo_image'   => array( 'label' => 'Background image (optional)', 'type' => 'image', 'default' => '' ),
			),
		),
		'home_testimonials' => array(
			'title'       => 'Testimonials',
			'panel'       => 'ridgeline_home',
			'description' => 'One review per line: Quote | Name | Town.',
			'fields'      => array(
				'reviews_heading' => array( 'label' => 'Heading', 'default' => 'What customers say' ),
				'reviews_list'    => array( 'label' => 'Reviews', 'type' => 'textarea', 'default' => "Bought a zero-turn online, called with a question, picked it up the next morning. Painless. | Dale M. | Boiling Springs\nThey fixed my snow blower in three days, and told me honestly it wasn’t worth replacing. | Karen P. | Newville\nGenerator went in before the storm. Staff walked me through the transfer switch. | Tom R. | Mechanicsburg" ),
			),
		),
		'home_compare' => array(
			'title'       => 'Compare Specs Table',
			'panel'       => 'ridgeline_home',
			'description' => 'Columns: one per line as Brand | Model. Rows: Spec | value 1 | value 2 | value 3 | value 4 | best (high, low, or none).',
			'fields'      => array(
				'cmp_eyebrow' => array( 'label' => 'Eyebrow', 'default' => 'Compare specs' ),
				'cmp_heading' => array( 'label' => 'Heading', 'default' => 'Which mower fits your acreage?' ),
				'cmp_text'    => array( 'label' => 'Intro', 'type' => 'textarea', 'default' => 'Four of our best sellers, side by side. Best value in each row is highlighted.' ),
				'cmp_columns' => array( 'label' => 'Columns', 'type' => 'textarea', 'default' => "Kestrel | ZT-60 Zero-Turn\nKestrel | ZT-48 Zero-Turn\nIronwood | R54 Garden Tractor\nIronwood | R42 Lawn Tractor" ),
				'cmp_rows'    => array( 'label' => 'Rows', 'type' => 'textarea', 'default' => "Cutting deck | 60\" | 48\" | 54\" | 42\" | high\nEngine power | 24 HP | 22 HP | 24 HP | 18.5 HP | high\nEngine type | V-twin OHV | V-twin OHV | V-twin OHV | Single OHV | none\nTop speed | 8 mph | 7 mph | 5.5 mph | 5 mph | high\nPrice | $5,499 | $4,299 | $4,299 | $2,799 | low\nUnits in stock | 7 | 5 | 3 | 4 | high" ),
				'cmp_button'  => array( 'label' => 'Button', 'default' => 'Compare all mowers →' ),
				'cmp_url'     => array( 'label' => 'Button link', 'type' => 'url', 'default' => '/shop/' ),
			),
		),
		'home_contact' => array(
			'title'  => 'Store Contact Strip',
			'panel'  => 'ridgeline_home',
			'fields' => array(
				'contact_strip_heading' => array( 'label' => 'Heading', 'default' => 'Visit or call the shop' ),
			),
		),

		/* ---------- Inner pages ---------- */
		'page_about'   => array(
			'title'  => 'About Page',
			'panel'  => 'ridgeline_pages',
			'fields' => array(
				'about_eyebrow' => array( 'label' => 'Eyebrow', 'default' => 'About Ridgeline' ),
				'about_heading' => array( 'label' => 'Heading', 'default' => 'Since 1974, the shop that fixes what it sells.' ),
				'about_lead'    => array( 'label' => 'Lead text', 'type' => 'textarea', 'default' => 'Three generations, one address, and a service bay that has never closed for a season.' ),
				'about_image'   => array( 'label' => 'Hero image', 'type' => 'image', 'default' => '' ),
				'about_stats'   => array( 'label' => 'Stats (Number | Label per line)', 'type' => 'textarea', 'default' => "50+ | Years in business\n8 | Brands we service\n3 | Store locations\n12k | Machines serviced" ),
			),
		),
		'page_contact' => array(
			'title'  => 'Contact Page',
			'panel'  => 'ridgeline_pages',
			'fields' => array(
				'contact_heading'   => array( 'label' => 'Heading', 'default' => 'Contact us' ),
				'contact_intro'     => array( 'label' => 'Intro', 'type' => 'textarea', 'default' => 'Questions about a machine, a part or a repair? Send a message or call the counter.' ),
				'contact_recipient' => array( 'label' => 'Send messages to (empty uses dealer email)', 'type' => 'email', 'default' => '' ),
				'contact_from_name' => array( 'label' => 'Sender name (empty uses site name)', 'default' => '' ),
				'contact_from'      => array( 'label' => 'Sender email (empty uses admin email)', 'type' => 'email', 'default' => '' ),
				'contact_subject'   => array( 'label' => 'Email subject', 'default' => 'New contact message' ),
				'contact_success'   => array( 'label' => 'Success message', 'default' => 'Thanks — your message is on its way. We reply within one business day.' ),
				'contact_error'     => array( 'label' => 'Error message', 'default' => 'We couldn’t send your message. Please call us instead.' ),
				'map_embed'         => array( 'label' => 'Google Maps embed URL (contact page, footer, home)', 'type' => 'url', 'default' => '' ),
			),
		),
		'page_service' => array(
			'title'       => 'Service Page',
			'panel'       => 'ridgeline_pages',
			'description' => 'Services: one per line as Title | Price | Description.',
			'fields'      => array(
				'service_heading' => array( 'label' => 'Heading', 'default' => 'Service & repair' ),
				'service_intro'   => array( 'label' => 'Intro', 'type' => 'textarea', 'default' => 'Factory-trained technicians for every brand we sell. Most repairs back in five business days.' ),
				'service_list'    => array( 'label' => 'Services', 'type' => 'textarea', 'default' => "Seasonal tune-up | From $49 | Oil, filter, plug, blades and a full inspection.\nBlade sharpening | From $59 | Balanced and sharpened, same-day on most.\nEngine repair | From $69 | Diagnosis and repair by factory-trained techs.\nSnow blower service | Call for quote | Seasonal service and repair for all brands.\nChainsaw service | From $89 | Chain sharpening, carburetor and tune-up.\nGenerator service | From $99 | Inspection, load test and maintenance.\nWarranty repair | From $109 | Authorized warranty center for the brands we carry.\nPickup & delivery | Call for quote | Within 25 miles, scheduled by phone." ),
				'service_recipient' => array( 'label' => 'Send service requests to (empty uses dealer email)', 'type' => 'email', 'default' => '' ),
			),
		),
		'page_financing' => array(
			'title'       => 'Financing Page',
			'panel'       => 'ridgeline_pages',
			'description' => 'Benefits: Number | Heading | Text. Partners: Name | Description | Apply URL.',
			'fields'      => array(
				'fin_heading'  => array( 'label' => 'Heading', 'default' => 'Financing' ),
				'fin_intro'    => array( 'label' => 'Intro', 'type' => 'textarea', 'default' => 'Take it home today and pay over time. Apply online or at the counter.' ),
				'fin_benefits' => array( 'label' => 'Benefits', 'type' => 'textarea', 'default' => "0% | Intro APR | Up to 36 months on select tractors.\n5 min | Quick decision | Apply online or at the counter.\n$99 | Low monthly | Payments on machines from $1,500." ),
				'fin_partners' => array( 'label' => 'Partners', 'type' => 'textarea', 'default' => "FIELDPAY | Equipment financing for homeowners and small businesses. Fixed rates, no prepayment penalty. | #\nROWCREDIT | Revolving credit for parts and service. Promotional financing on purchases over $500. | #" ),
			),
		),

		/* ---------- Commerce & integrations ---------- */
		'commerce'     => array(
			'title'  => 'Store & Products',
			'panel'  => 'ridgeline_integrations',
			'fields' => array(
				'shop_title'        => array( 'label' => 'Shop heading', 'default' => 'All Products' ),
				'shop_intro'        => array( 'label' => 'Shop intro (use {count})', 'type' => 'textarea', 'default' => 'Browse {count} machines, parts and accessories from the brands we sell and service.' ),
				'shop_per_page'     => array( 'label' => 'Products per page', 'type' => 'number', 'default' => 12 ),
				'shop_banner_text'  => array( 'label' => 'Shop banner headline (empty hides it)', 'default' => '' ),
				'shop_banner_image' => array( 'label' => 'Shop banner image', 'type' => 'image', 'default' => '' ),
				'compare_enabled'   => array( 'label' => 'Enable product compare', 'type' => 'checkbox', 'default' => true ),
				'pickup_notice'     => array( 'label' => 'Product pickup notice', 'default' => 'Free pickup at any Ridgeline location. Most orders are ready in 2 hours. Delivery quotes available by phone.' ),
				'call_price_meta'   => array( 'label' => 'Call-for-price meta key', 'default' => '_tw_hide_price' ),
				'stock_widget'      => array( 'label' => 'Show the TargetWeb stock-by-location widget area', 'type' => 'checkbox', 'default' => true ),
				'locations'         => array( 'label' => 'Store locations (Name | Address per line, used when the widget is not loaded)', 'type' => 'textarea', 'default' => "Carlisle | 1840 Quarry Road, Carlisle, PA 17013" ),
			),
		),
		'integrations' => array(
			'title'  => 'Tracking & CRM',
			'panel'  => 'ridgeline_integrations',
			'fields' => array(
				'ga4_id'           => array( 'label' => 'GA4 measurement ID', 'default' => '' ),
				'gtm_id'           => array( 'label' => 'Google Tag Manager ID', 'default' => '' ),
				'targetcrm_client' => array(
					'label'       => 'Constellation widget client ID',
					'description' => 'Optional. Loads the same app.constellationdealer.com widget used by the first ecommerce theme. Lead-form API settings remain under Products → TargetWeb CRM.',
					'default'     => '',
				),
			),
		),
		'seo'          => array(
			'title'       => 'SEO',
			'panel'       => 'ridgeline_integrations',
			'description' => 'Used only when Yoast, Rank Math, All in One SEO, SEOPress, Slim SEO or The SEO Framework is not active.',
			'fields'      => array(
				'seo_home_description' => array( 'label' => 'Homepage meta description', 'type' => 'textarea', 'default' => '' ),
				'seo_share_image'      => array( 'label' => 'Default share image (1200×630)', 'type' => 'image', 'default' => '' ),
				'seo_business_type'    => array( 'label' => 'Business type (schema.org)', 'default' => 'Store' ),
			),
		),
	);

	return $settings;
}

/**
 * Three hero slides with the same fields each.
 */
function ridgeline_hero_fields() {
	$defaults = array(
		1 => array( 'Season opener', 'Get the yard done before the leaves win.', 'Zero-turns and tractors, in stock and ready to go. Save up to $400 on select Kestrel mowers through October.', 'Shop mowers', '/shop/' ),
		2 => array( 'Snow season', 'Two-stage power for long driveways.', 'Pre-season pricing on Northfield snow blowers. Assembled, fueled and ready on day one.', 'Shop snow blowers', '/shop/' ),
		3 => array( 'Service department', 'Tune-ups done right, in five days.', 'Factory-trained techs, parts on the shelf, and pickup within 25 miles.', 'Book service', '/service/' ),
	);
	$fields = array(
		'hero_autoplay' => array( 'label' => 'Autoplay slides', 'type' => 'checkbox', 'default' => true ),
		'hero_call'     => array( 'label' => 'Show call button on slides', 'type' => 'checkbox', 'default' => true ),
	);
	foreach ( $defaults as $n => $d ) {
		$fields[ "hero{$n}_on" ]      = array( 'label' => "Slide {$n}: show", 'type' => 'checkbox', 'default' => true );
		$fields[ "hero{$n}_eyebrow" ] = array( 'label' => "Slide {$n}: eyebrow", 'default' => $d[0] );
		$fields[ "hero{$n}_heading" ] = array( 'label' => "Slide {$n}: heading", 'default' => $d[1] );
		$fields[ "hero{$n}_text" ]    = array( 'label' => "Slide {$n}: text", 'type' => 'textarea', 'default' => $d[2] );
		$fields[ "hero{$n}_button" ]  = array( 'label' => "Slide {$n}: button", 'default' => $d[3] );
		$fields[ "hero{$n}_url" ]     = array( 'label' => "Slide {$n}: link", 'type' => 'url', 'default' => $d[4] );
		$fields[ "hero{$n}_image" ]   = array( 'label' => "Slide {$n}: desktop image (2400×1000)", 'type' => 'image', 'default' => '' );
		$fields[ "hero{$n}_mobile" ]  = array( 'label' => "Slide {$n}: mobile image (800×1000, optional)", 'type' => 'image', 'default' => '' );
	}
	return $fields;
}

/**
 * Manual homepage category cards.
 */
function ridgeline_manual_category_fields() {
	$fields = array();
	for ( $n = 1; $n <= 8; $n++ ) {
		$fields[ "cat{$n}_title" ] = array( 'label' => "Manual card {$n}: title", 'default' => '' );
		$fields[ "cat{$n}_image" ] = array( 'label' => "Manual card {$n}: image", 'type' => 'image', 'default' => '' );
		$fields[ "cat{$n}_url" ]   = array( 'label' => "Manual card {$n}: link", 'type' => 'url', 'default' => '' );
	}
	return $fields;
}

/**
 * Flat key => field map.
 */
function ridgeline_field_map() {
	static $map = null;
	if ( null === $map ) {
		$map = array();
		foreach ( ridgeline_settings() as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$map[ $key ] = $field;
			}
		}
	}
	return $map;
}

/**
 * Read a theme setting with its registered default.
 *
 * @param string $key Field key without the ridgeline_ prefix.
 * @return mixed
 */
function ridgeline_mod( $key ) {
	$map     = ridgeline_field_map();
	$default = isset( $map[ $key ]['default'] ) ? $map[ $key ]['default'] : '';
	return get_theme_mod( 'ridgeline_' . $key, $default );
}

function ridgeline_sanitize_checkbox( $value ) {
	return ! empty( $value );
}

function ridgeline_sanitize_callback( $field ) {
	$type = isset( $field['type'] ) ? $field['type'] : 'text';
	switch ( $type ) {
		case 'textarea':
			return 'sanitize_textarea_field';
		case 'checkbox':
			return 'ridgeline_sanitize_checkbox';
		case 'number':
			return 'absint';
		case 'color':
			return 'sanitize_hex_color';
		case 'email':
			return 'sanitize_email';
		case 'url':
		case 'image':
			return 'esc_url_raw';
		case 'select':
			$choices = $field['choices'];
			$default = $field['default'];
			return static function ( $value ) use ( $choices, $default ) {
				return array_key_exists( $value, $choices ) ? $value : $default;
			};
		default:
			return 'sanitize_text_field';
	}
}

/**
 * Register panels, sections and controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer manager.
 */
function ridgeline_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport        = 'refresh';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'refresh';

	$panels = array(
		'ridgeline_global'       => array( 'Site-Wide Settings', 5 ),
		'ridgeline_home'         => array( 'Homepage', 6 ),
		'ridgeline_pages'        => array( 'Inner Pages', 7 ),
		'ridgeline_integrations' => array( 'Store & Integrations', 8 ),
	);
	foreach ( $panels as $id => $panel ) {
		$wp_customize->add_panel( $id, array( 'title' => $panel[0], 'priority' => $panel[1] ) );
	}

	$priority = 10;
	foreach ( ridgeline_settings() as $section_id => $section ) {
		$wp_customize->add_section(
			'ridgeline_' . $section_id,
			array(
				'title'       => $section['title'],
				'panel'       => $section['panel'],
				'description' => isset( $section['description'] ) ? $section['description'] : '',
				'priority'    => $priority++,
			)
		);

		foreach ( $section['fields'] as $key => $field ) {
			$setting_id = 'ridgeline_' . $key;
			$type       = isset( $field['type'] ) ? $field['type'] : 'text';
			$wp_customize->add_setting(
				$setting_id,
				array(
					'default'           => $field['default'],
					'sanitize_callback' => ridgeline_sanitize_callback( $field ),
					'transport'         => 'refresh',
				)
			);

			$args = array(
				'label'       => $field['label'],
				'description' => isset( $field['description'] ) ? $field['description'] : '',
				'section'     => 'ridgeline_' . $section_id,
			);

			if ( 'color' === $type ) {
				$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $setting_id, $args ) );
			} elseif ( 'image' === $type ) {
				$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $setting_id, $args ) );
			} else {
				$args['type'] = $type;
				if ( 'select' === $type ) {
					$args['choices'] = $field['choices'];
				}
				if ( ! empty( $field['input_attrs'] ) ) {
					$args['input_attrs'] = $field['input_attrs'];
				}
				$wp_customize->add_control( $setting_id, $args );
			}
		}
	}

	$wp_customize->remove_section( 'colors' );
	$wp_customize->remove_section( 'background_image' );
}
add_action( 'customize_register', 'ridgeline_customize_register' );
