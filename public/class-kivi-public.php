<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://kivi.etuovi.com/
 * @since      1.0.0
 *
 * @package    Kivi
 * @subpackage Kivi/public
 * @author     hetenho <henrik.tenhovirta@gofore.com>
 */


class Kivi_Public {

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $plugin_name    The ID of this plugin.
   */
  private $plugin_name;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $plugin_name       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct( $plugin_name, $version ) {

    $this->plugin_name = $plugin_name;
    $this->version = $version;

  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles() {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in Kivi_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The Kivi_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/kivi-public.css', array('slick-theme'), $this->version, 'all' );
    wp_enqueue_style( 'slick', plugin_dir_url( __FILE__ ) . 'css/slick.css', array(), $this->version, 'all' );
    wp_enqueue_style( 'slick-theme', plugin_dir_url( __FILE__ ) . 'css/slick-theme.css', array('slick'), $this->version, 'all' );

  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts() {
		 wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/kivi-public.js', array( 'jquery', 'slick' ), $this->version, false );
		 wp_enqueue_script( 'slick', plugin_dir_url( __FILE__ ) . 'js/slick.min.js', array( 'jquery' ), $this->version, false );
  }

	public function jquery_version_bump() {
		if (!is_admin()) {
			wp_deregister_script('jquery');
			wp_register_script('jquery', plugin_dir_url( __FILE__ ) . 'js/jquery.min.js', false, '1.8.1');
			wp_enqueue_script('jquery');
		}
  }

  public function kivi_item_metaboxes( array $meta_boxes ) {
    $fields = array(
      array(
        'id'   => '_realty_unique_no',
        'name' => __('realty_unique_no', 'kivi_item'),
        'type' => 'text',
      ),
      array(
        'id'   => '_updatedate',
        'name' => __('updatedate', 'kivi_item'),
        'type' => 'text',
      ),
      array(
        'id'   => '_flat_structure',
        'name' => __('flat structure', 'kivi_item'),
        'type' => 'text',
      ),
      array(
        'id'   => '_street',
        'name' => __('street', 'kivi_item'),
        'type' => 'text',
      ),
      array(
        'id'   => '_addr_town_area',
        'name' => __('addr_town_area', 'kivi_item'),
        'type' => 'text',
      ),
      array(
        'id'   => '_kivi_item_image',
        'name' => __('Item Image', 'kivi_item'),
        'type' => 'image',
        'repeatable' => true,
        'repeatable_max' => 30,
      ),
    );
      $meta_boxes[] = array(
          'title' => __('KIVI item data', 'kivi_item'),
          'pages' => 'kivi_item',
          'context'    => 'side',
          'priority'   => 'high',
          'fields' => $fields
      );
      return $meta_boxes;
  }

  /**
   * Get item price in a clean format. For rental assignment returns rent and for sale unencumbered price.
   * @param $post_id
   * @param string $postfix default " €"
   * @return string price or empty string
   */
  public static function get_display_price($post_id , $postfix = " €" ) {
    $price = "";
    if( self::is_for_rent_assignment( $post_id ) ) {
      $rent_price = get_post_meta($post_id, '_assignmentrent_rent', true);
      if( $rent_price ) {
        $price = $rent_price;
      }
    }
    else{
      $price = get_post_meta($post_id, '_unencumbered_price', true);
    }

    if( $price ){

      if( is_float($price) || intval($price) < 1000 ){
        $price = number_format($price, 2, ',', ' '); // to 2 decimal price string with thousand separators
      }
      elseif( intval($price) > 999 ){
        $price = intval($price);
        $price = number_format($price, 0, '', ' '); // with thousands separated
      }

      return $price.$postfix;
    }
    return "";
  }


  /**
   * Return boolean true if assignment type is for rent
   * @param $post_id
   * @return bool
   */
  public static function is_for_rent_assignment( $post_id ) {
    $assigntype = get_post_meta($post_id, '_assignment_type', true);
    if( ! empty($assigntype) ){
      if( strpos($assigntype, 'vuokranantaja') !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Return boolean true if assignment type is property ( ei osake vaan kiinteistö )
   * @param $post_id
   * @return bool
   */
  public static function is_property_assignment($post_id ) {
    $assigntype = get_post_meta($post_id, '_assignment_type', true);
    if( ! empty($assigntype) ){
      if( strpos($assigntype, 'kiinteistö') !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Return boolean true if assignment type is new building
   * @param $post_id
   * @return bool
   */
  public static function is_newbuild_assignment($post_id ) {
    $assigntype = get_post_meta($post_id, '_assignment_type', true);
    if( ! empty($assigntype) ){
      if( strpos($assigntype, 'uudiskohde') !== false) {
        return true;
      }
    }
    return false;
  }

  /**
   * Return a string used inside class element of a kivi item.
   * @param $post_id
   * @return string
   */
  public static function get_css_classes( $post_id ) {
    $classes = array();
    $classes[] = "kivi-no-".get_post_meta( $post_id, '_realty_unique_no', true );

    $bid_wanted_flag = get_post_meta( $post_id, "_bid_wanted_flag", true );
    if( !empty($bid_wanted_flag) && $bid_wanted_flag != 'false' ){
      $classes[] ="bid-wanted";
    }

    if( is_array(self::get_next_presentation( $post_id )) ){
      $classes[] = "has-presentation";
    }

    $type = get_post_meta($post_id, '_realtytype_id', true);
    if( ! empty($type) ){
      $classes[] = "type-".trim(sanitize_title($type));
    }

    if( self::is_for_rent_assignment( $post_id ) ) {
      $classes[] = "assignment-for-rent";
    }

    if( self::is_property_assignment( $post_id ) ) {
      $classes[] = "assignment-property";
    }

    if( self::is_newbuild_assignment( $post_id ) ) {
      $classes[] = "assignment-for-new-building";
    }

    $itemgroup = get_post_meta($post_id, '_itemgroup_id', true);
    if( ! empty($itemgroup) ){
      $classes[] = "itemgroup-".trim($itemgroup);
    }

    $town = get_post_meta($post_id, '_town', true);
    if( ! empty($town) ){
      $classes[] = "town-".trim(sanitize_title($town));
    }

    $lotholding = get_post_meta($post_id, '_rc_lotholding_id', true);
    if( ! empty($lotholding) ){
      $classes[] = "lotholding-".trim($lotholding);
    }

    $tradetype = get_post_meta($post_id, '_tradetype_id', true);
    if( ! empty($tradetype) ){
      $classes[] = "tradetype-".trim($tradetype);
    }

    $publish_date = get_post_meta($post_id, '_homepage_publish_date', true);
    if( $publish_date > date('Y-m-d H:i:s', strtotime('-1 days')) ){
      $classes[] = "new-24h";
    }
    elseif( $publish_date > date('Y-m-d H:i:s', strtotime('-7 days')) ){
      $classes[] = "new-7d";
    }
    elseif( $publish_date > date('Y-m-d H:i:s', strtotime('-14 days')) ){
      $classes[] = "new-14d";
    }
    elseif( $publish_date > date('Y-m-d H:i:s', strtotime('-30 days')) ){
      $classes[] = "new-30d";
    }

    $assigntype = get_post_meta($post_id, '_assignment_type', true);
    if( ! empty($assigntype) ){
      $classes[] = "assigment-type-".trim(sanitize_title($assigntype));
    }

    $investment = get_post_meta($post_id, '_investment_flag', true);
    if( ! empty($investment) ){
      $classes[] = "investment-item";
    }

    $video = get_post_meta($post_id, '_realty_vi_presentation', true);
    if( ! empty($video) ){
      $classes[] = "video-pre";
    }

    $presentations_arr = get_post_meta( $post_id, '_vi_presentations', true );
    if( ! empty($presentations_arr) && is_array($presentations_arr) ) {
      foreach( $presentations_arr as $presentation ) {
        if ( ! empty($presentation['vi_pre_extralink_seq'])) {
          $classes[] = "has-pre-extra-info";
        }
        if ( ! empty($presentation['vi_pre_video_flag'])) {
          $classes[] = "has-pre-video";
        }
        if ( empty($presentation['vi_pre_video_flag']) && empty($presentation['vi_pre_extralink_seq'])) {
          $classes[] = "has-pre-virtual";
        }
      }
    }

    return esc_attr(" ".implode(" ", $classes));
  }

  /**
   * Returns future presentations if any. A presentation held earlier today is considered future.
   * @param $post_id
   * @param bool $strict True future only
   * @return mixed
   */
  static function get_future_presentations($post_id, $strict = false ){
    $presentations = get_post_meta( $post_id, '_presentations', true );

	if( ! is_array($presentations) ) {
		return "";
	}

    foreach($presentations as $key => $presentation){

      if($strict){
        if ($presentation['presentation_end'] < date("Y-m-d H:i:s")) {
          unset($presentations[$key]);
        }
      }
      else {
        if ($presentation['presentation_date'] < date("Y-m-d H:i:s")) {
          unset($presentations[$key]);
        }
      }
    }
    return $presentations;
  }

  /**
   * Return next presentation: today or in the future.
   * @param $post_id
   * @return mixed
   */
  public static function get_next_presentation($post_id ){
    $presentations = self::get_future_presentations( $post_id );
	if( is_array($presentations) ) {
		usort($presentations, function($a, $b) {
		  return $a['presentation_date'] - $b['presentation_date'];
		});
		return reset($presentations);
	}
	return "";
  }

  static function map_post_meta($meta_field) {
    $meta_field = substr($meta_field, 1);
    $arr = array(
      "addr_region_area_id" => __("Maakunta","kivi"),
      "addr_town_area" => __("Kunta","kivi"),
      "arable_no_flag" => __("Ei peltoa","kivi"),
      "arableland" => __("Pellon kuvaus","kivi"),
      "arableland_area_ha" => __("Peltoala ha","kivi"),
      "constructionright" => __("Lisätietoja rakennusoikeudesta","kivi"),
      "constructionright_e" => __("Rakennusoikeus e-luku","kivi"),
      "constructionright_m2" => __("Rakennusoikeus, m²","kivi"),
      "country_id" => __("Maa","kivi"),
      "drainedflag" => __("Salaojitettu","kivi"),
      "estatename" => __("Kortteli/kiinteistön nimi","kivi"),
      "extra_realtytype_id" => __("Kiinteistön tyyppi","kivi"),
      "forestarea_info" => __("Metsän kuvaus","kivi"),
      "foresteconomyplanflag" => __("Metsätaloussuunnitelma olemassa","kivi"),
      "itemgroup_id" => __("Tuoteryhmä","kivi"),
      "locx" => __("Karttakoordinaatti x","kivi"),
      "locy" => __("Karttakoordinaatti y","kivi"),
      "logging_potential_m3" => __("Välittömät hakkuumahdollisuudet, m3","kivi"),
      "loggingpotential_info" => __("Hakkuumahdollisuuksien kuvaus","kivi"),
      "lot_measuretype_id" => __("Tontin pinta-alan yksikkö","kivi"),
      "lotno" => __("Tontin numero","kivi"),
      "lottype_id" => __("Tontin tyyppi","kivi"),
      "lot_info" => __("Lisätietoja tontista","kivi"),
      "otherspaces" => __("Muut tilat (säiliöelementti)","kivi"),
      "otherspace" => __("Muu tila","kivi"),
      "postarea" => __("Postitoimipaikka","kivi"),
      "postcode" => __("Postinumero","kivi"),
      "quarteroftown" => __("Kaupunginosa/kylä","kivi"),
      "rc_lotholding_id" => __("Tontin omistus","kivi"),
      "realtyidentifier" => __("Kiinteistötunnus/laitostunnus","kivi"),
      "realtytype_id" => __("Tyyppi","kivi"),
      "search_itemgroup_id" => __("Hauissa mukana myös tuoteryhmissä","kivi"),
      "street" => __("Katuosoite","kivi"),
      "street_name" => __("Katu","kivi"),
      "street_number" => __("Talon numero","kivi"),
      "town" => __("Kaupunki","kivi"),
      "transfer_restriction" => __("Kohteen käyttö- ja luovutusrajoitukset","kivi"),
      "arealvl_id" => __("Alueen taso","kivi"),
      "water_area_desc" => __("Vesistön nimi","kivi"),
      "water_quality_desc" => __("Veden laatu ja riittävyys","kivi"),
      "waterfront_length_m" => __("Omaa rantaviivaa, metriä","kivi"),
      "waterfronttype" => __("Rannan tyyppi","kivi"),
      "waterfront_type_desc" => __("Rannan kuvaus","kivi"),
      "woodland_area_ha" => __("Metsäala ha","kivi"),
      "woodland_no_flag" => __("Ei metsää","kivi"),
      "zoning_id" => __("Kaavoitus","kivi"),
      "zoning_info" => __("Lisätietoa kaavoituksesta","kivi"),
      "building_info" => __("Lisätietoa muista rakennuksista","kivi"),
      "buildingtype_id" => __("Muut rakennukset","kivi"),
      "condition" => __("Lisätietoa kunnosta","kivi"),
      "constructionmaterial_desc" => __("Rakennusmateriaalien kuvaus","kivi"),
      "floor_area_m2" => __("Kerrosala m²","kivi"),
      "heating" => __("Lämmitysjärjestelmä ja sen kunto","kivi"),
      "living_area_m2" => __("Asuinpinta-ala m²","kivi"),
      "other_area_m2" => __("Muut tilat m²","kivi"),
      "rc_buildyear" => __("Rakennusvuosi (aloitettu)","kivi"),
      "rc_buildyear2" => __("Rakennusvuosi (päättynyt)","kivi"),
      "rc_usageyear" => __("Rakennuksen käyttöönottovuosi","kivi"),
      "renovation_made" => __("Tehdyt korjaukset","kivi"),
      "roof_condition" => __("Katon kunto","kivi"),
      "total_area_m2" => __("Yhteensä m²","kivi"),
      "charges_eheating" => __("(Sähkölämmityksen) Keskimääräinen kokonaiskustannus","kivi"),
      "charges_heating" => __("Lämmityskustannukset, muu kuin sähkö (€/kk)","kivi"),
      "charges_other" => __("Lisätietoa maksuista","kivi"),
      "charges_realtytax" => __("Kiinteistövero","kivi"),
      "charges_road" => __("Tiemaksut","kivi"),
      "charges_sewage" => __("Vesi ja jätevesi","kivi"),
      "charges_streetcleansing" => __("Puhtaanapito €/kk","kivi"),
      "charges_tv" => __("Satelliittiantenni/Kaapeli-TV (€/kk)","kivi"),
      "eheating_flag" => __("Sähkölämmitys","kivi"),
      "mortgage_amount" => __("Velkakiinnitykset","kivi"),
      "mortgage_free_amount" => __("Vapaana olevat velkakiinnitykset","kivi"),
      "deeds_safe_keeping" => __("Velkakirjojen säilytyspaikka","kivi"),
      "deeds_amount" => __("Panttivastuu","kivi"),
      "encumbrance" => __("Kirjatut muut rasitukset, rasitteet ja valinnanrajoitukset","kivi"),
      "possession_partition_flag" => __("Kirjattu hallinnonjakosopimus","kivi"),
      "unpaid_bills_amount" => __("Maksamattomat maksut","kivi"),
      "encumbrance_reported_flag" => __("Toimeksiantaja on ilmoittanut kaikki sellaiset kiinteistöön kohdistuvat…","kivi"),
      "encumbrance_desc" => __("Lisätietoa edellisestä","kivi"),
      "area_desc" => __("Lisätietoja pinta-alasta","kivi"),
      "areabasis_id" => __("Pinta-alan peruste","kivi"),
      "door_number" => __("Huoneisto","kivi"),
      "fireplace_other" => __("Tulisija","kivi"),
      "flat_structure" => __("Huoneistoselitelmä","kivi"),
      "flattype_id" => __("Huoneluku, hakuehtona Internetissä","kivi"),
      "floor" => __("Kerros","kivi"),
      "floors" => __("Kerroksia","kivi"),
      "holdingtype_id" => __("Hallintamuoto","kivi"),
      "holdingtype_other" => __("Hallintamuoto, muu","kivi"),
      "living_area_m2" => __("Asuinpinta-ala m²","kivi"),
      "other_area_m2" => __("Muut tilat m²","kivi"),
      "owningtype_id" => __("Omistusmuoto","kivi"),
      "presentation" => __("Esittelyteksti","kivi"),
      "stairway" => __("Rappu","kivi"),
      "stock_numbers" => __("Osakesarjojen numerot","kivi"),
      "total_area_m2" => __("Yhteensä m²","kivi"),
      "view_desc" => __("Näkymät huoneistosta","kivi"),
      "charges_eheating" => __("(Sähkölämmityksen) Keskimääräinen kokonaiskustannus","kivi"),
      "charges_finance_base_month" => __("Rahoitusvastike","kivi"),
      "charges_maint_base_month" => __("Hoitovastike","kivi"),
      "charges_other" => __("Lisätietoja maksuista","kivi"),
      "charges_parkingspace" => __("Autopaikkamaksu","kivi"),
      "charges_sauna" => __("Saunamaksu","kivi"),
      "charges_tv" => __("Satelliittiantenni/Kaapeli-TV (€/kk)","kivi"),
      "charges_water" => __("Vesimaksu (€/kk)","kivi"),
      "charges_condominium_total_mo" => __("Yhtiövastike","kivi"),
      "chargesmaint2_month" => __("Yhtiövastike","kivi"),
      "claim_right_company" => __("Lunastusoikeus yhtiöllä","kivi"),
      "claim_right_shareholder" => __("Lunastusoikeus osakkailla","kivi"),
      "eheating_flag" => __("Sähkölämmitys","kivi"),
      "parkingcharge_type_id" => __("Autopaikkamaksun tarkenne","kivi"),
      "transfer_restriction" => __("Muut käyttö- ja luovutusrajoitukset","kivi"),
      "watercharge_type_id" => __("Vesimaksun tarkenne","kivi"),
      "rc_carplug_count" => __("(Taloyhtiön) Sähköpistokepaikkoja kpl","kivi"),
      "rc_carshelter_count" => __("Autokatospaikkoja","kivi"),
      "rc_constructionmaterial_other" => __("Taloyhtiön Rakennusmateriaali, muu","kivi"),
      "rc_energy_flag" => __("Energiatodistus","kivi"),
      "rc_energyclass" => __("Lisätietoja energiatodistuksesta","kivi"),
      "energyclass_name" => __("Energialuokka","kivi"),
      "rc_expense_oblications" => __("Kustannuksia myöhemmin aiheuttavat velvoitteet esim. autopaikat","kivi"),
      "rc_garage_count" => __("Autotallipaikkoja","kivi"),
      "rc_has_other" => __("Taloyhtiössä muuta","kivi"),
      "rc_housemanager" => __("Isännöitsijän yhteystiedot","kivi"),
      "rc_lot_area_m2" => __("Tontin pinta-ala","kivi"),
      "rc_lot_rent" => __("Tontin vuokra","kivi"),
      "rc_lot_renter" => __("Tontin Vuokranantaja","kivi"),
      "rc_lot_renttime" => __("Taloyhtiön (Tontin) Vuokrasopimus päättyy","kivi"),
      "rc_lotholding_id" => __("Tontin omistus","kivi"),
      "rc_maintenance_id" => __("Taloyhtiön Kiinteistön hoidosta vastaa","kivi"),
      "rc_maintenance_other" => __("Taloyhtiön Kiinteistönhoidon lisätiedot","kivi"),
      "rc_parkingspace_count" => __("(Taloyhtiön) Piha-autopaikkoja kpl","kivi"),
      "rc_renovation_decision_flag" => __("Päätös tehty tiedossa olevista korjauksista/remontista","kivi"),
      "rc_renovation_made" => __("(Taloyhtiön) Rakennukseen tehdyt korjaukset ja remontit","kivi"),
      "rc_renovation_planned" => __("Tiedossa olevat tulevat korjaukset/remontit","kivi"),
      "rc_repair_need" => __("(Taloyhtiön) Rakennuksissa havaitut puutteellisuudet, virheellisyydet ja korjaustarpeet","kivi"),
      "rc_roof" => __("Taloyhtiön Kattotyyppi","kivi"),
      "rc_roofing" => __("Taloyhtiön Kate","kivi"),
      "rc_usageyear" => __("Käyttöönottovuosi","kivi"),
      "rc_wastewater_flag" => __("Jätevesiasetuksen mukainen selvitys, suunnitelma sekä käyttö- ja huolto-ohje","kivi"),
      "rc_zoning_info" => __("(Taloyhtiön) Lisätietoa kaavoituksesta","kivi"),
      "maintenance_desc" => __("Taloyhtiön kiinteistön hoidosta vastaa","kivi"),
      "realtycompany" => __("Taloyhtiön nimi","kivi"),
      "rc_debt" => __("(Taloyhtiön) Lainat","kivi"),
      "rc_mortgage" => __("(Taloyhtiön) Kiinnitykset","kivi"),
      "rc_mortgage_date" => __("(Taloyhtiön kiinnitykset) Isännöitsijän todistuksen mukaisena päivämääränä","kivi"),
      "bathroom_desc" => __("Pesutilojen kuvaus, lisätiedot ja varustus","kivi"),
      "bedroom_count" => __("Makuuhuoneiden lukumäärä","kivi"),
      "bedroom_desc" => __("Makuuhuoneen kuvaus, lisätiedot ja varustus","kivi"),
      "condition" => __("Lisätietoa kunnosta","kivi"),
      "condition_id" => __("Yleiskunto","kivi"),
      "floormaterial_info" => __("Lattiamateriaalien kuvaus ja lisätiedot","kivi"),
      "kitchen_equipment_desc" => __("Keittiön kuvaus, lisätiedot ja varustus","kivi"),
      "laundry" => __("Khh:n kuvaus, lisätiedot ja varustus","kivi"),
      "livingroom_info" => __("Oleskelutilojen kuvaus, lisätiedot ja varustus","kivi"),
      "not_sold_desc" => __("Kauppaan ei kuulu","kivi"),
      "other_equipment_desc" => __("Muuta kauppaan kuuluvaa","kivi"),
      "otherspace_desc" => __("Tilojen kuvaus","kivi"),
      "renovation_made" => __("Toimeksiantajan aikana huoneistoon tehdyt toimenpiteet sekä niiden ajankohta","kivi"),
      "roofmaterial_info" => __("Kattomateriaalien kuvaus ja lisätiedot","kivi"),
      "sauna_desc" => __("Saunan kuvaus, lisätiedot ja varustus","kivi"),
      "saunaoven_id" => __("Saunan kiuas","kivi"),
      "storage_condition" => __("Säilytystilojen kuvaus ja lisätiedot","kivi"),
      "toilet_count" => __("Vessojen lukumäärä","kivi"),
      "toilet_info" => __("WC:n kuvaus, lisätiedot ja varustus","kivi"),
      "wallmaterial_info" => __("Seinämateriaalien kuvaus ja lisätiedot","kivi"),
      "connections" => __("Liikenneyhteydet","kivi"),
      "daycare" => __("Päiväkoti","kivi"),
      "driveinstruction" => __("Ajo-ohje","kivi"),
      "other_important_info" => __("Muut kaupan kannalta merkittävät tiedot","kivi"),
      "school" => __("Koulut","kivi"),
      "services" => __("Palvelut","kivi"),
      "services_other" => __("Lisätietoa palveluista","kivi"),
      "bid_wanted_flag" => __("Onko kyseessä tarjouskauppa","kivi"),
      "bids_page_url" => __("Linkki kohteen seurantasivulle","kivi"),
      "highest_bid" => __("Korkein annettu tarjous","kivi"),
      "bids" => __("Annetut tarjoukset (säiliöelementti)","kivi"),
      "bid_price" => __("Annettu tarjous","kivi"),
      "expires_date" => __("Tarjous voimassa pvm asti","kivi"),
      "valid_date" => __("Tarjous voimassa pvm alkaen","kivi"),
      "conditions_flag" => __("Onko kyseessä ehdollinen tarjous","kivi"),
      "bid_customers" => __("Tarjoukseen liittyvät asiakkaat (säiliöelementti)","kivi"),
      "bid_customers_phone" => __("Asiakkaan puhelinnumero","kivi"),
      "bid_customer_id" => __("Asiakkaan id Kivi-järjestelmässä","kivi"),
      "bid_ciustomer_name" => __("Asiakkaan nimi","kivi"),
      "bid_customer_email" => __("Asiakkaan sähköposti","kivi"),
      "supplier_postarea" => __("Toimiston postitoimipaikka","kivi"),
      "supplier_email" => __("Sähköposti","kivi"),
      "supplier_homepage_url" => __("Kotisivun osoite","kivi"),
      "supplier_mobilephone" => __("Toimiston matkapuhelin","kivi"),
      "supplier_name" => __("Toimiston nimi","kivi"),
      "supplier_phone" => __("Toimiston lankapuhelin","kivi"),
      "supplier_postcode" => __("Postinumero","kivi"),
      "supplier_supplier_id" => __("Toimiston ID","kivi"),
      "supplier_street" => __("Lähiosoite","kivi"),
      "iv_person" => __("phone Välittäjän lankanumero","kivi"),
      "iv_person_image_url" => __("Välittäjän kuva","kivi"),
      "iv_person_mobilephone" => __("Välittäjän matkapuhelinnumero","kivi"),
      "iv_person_name" => __("Välittäjän nimi","kivi"),
      "iv_person_suppliername" => __("Välittäjän toimiston nimi","kivi"),
      "companyperson_lastname" => __("Välittäjän sukunimi","kivi"),
      "companyperson_person_id" => __("Välittäjän ID","kivi"),
      "companyperson_company_name" => __("Välittäjän toimiston nimi","kivi"),
      "companyperson_email" => __("Välittäjän sähköposti","kivi"),
      "companyperson_firstname" => __("Välittäjän etunimi","kivi"),
      "companyperson_mobilephone" => __("Välittäjän matkapuhelinnumero","kivi"),
      "sc_image_url" => __("Välittäjän kuva","kivi"),
      "sc_itempage_email" => __("Sähköposti kohdesivulla","kivi"),
      "person_mobilephone" => __("Toimeksiannon luojan matkapuhelin","kivi"),
      "active_flag" => __("Kohteen tila, aktiivinen tai passiivinen","kivi"),
      "area_max_m2" => __("Pinta-ala yhteensä, ylä","kivi"),
      "area_min_m2" => __("Pinta-ala yhteensä, ala","kivi"),
      "commonshow" => __("Yleisesittely","kivi"),
      "conference_room_id" => __("Kokoustila 1=kyllä, 2=ei, 3=ei tiedossa","kivi"),
      "conference_room_info" => __("Lisätietoja kokoustiloista","kivi"),
      "debtfreeprice_max" => __("Velatonhinta, ylä","kivi"),
      "debtfreeprice_min" => __("Velatonhinta, ala","kivi"),
      "info" => __("Muuta lisätietoa","kivi"),
      "investment_flag" => __("Sijoituskohde","kivi"),
      "lift_id" => __("Hissi 1=kyllä, 2=ei, 3=ei tiedossa","kivi"),
      "lift_info" => __("Lisätietoja hissistä","kivi"),
      "office_services" => __("Toimistopalvelut","kivi"),
      "other_rent_conditions" => __("Muut vuokraushedot","kivi"),
      "parking" => __("Pysäköinti","kivi"),
      "price_max" => __("Myyntihinta, ylä ja pakollinen","kivi"),
      "price_min" => __("Myyntihinta, ala","kivi"),
      "pricepublishing_id" => __("Hinta/Vuokratietojen käsittely","kivi"),
      "realty_assign_duration_date" => __("Sopimuksen kesto, pvm asti","kivi"),
      "realty_assign_duration_id" => __("Sopimuksen kesto","kivi"),
      "realty_free_type_date" => __("Kohde vapautuu, pvm","kivi"),
      "realty_free_type_id" => __("Kohde vapautuu","kivi"),
      "realty_free_type_other" => __("Kohde vapautuu, muu ehto","kivi"),
      "realtygroup_id" => __("Kategoria, johon toimitila kuuluu","kivi"),
      "realtytype_id" => __("Tyyppi","kivi"), // rivitalo, ...
      "rent_m2_month_max" => __("Neliövuokra, max","kivi"),
      "rent_m2_month_min" => __("Neliövuokra, min","kivi"),
      "rentprice_max" => __("Vuokrahinta, max","kivi"),
      "rentprice_min" => __("Vuokrahinta, min","kivi"),
      "restaurant_services" => __("Ravintolapalvelut","kivi"),
      "restroom_info" => __("Sosiaalitilat","kivi"),
      "spacechare" => __("Tilan jakaminen","kivi"),
      "streetlevel_flag" => __("Katutaso","kivi"),
      "targetname" => __("Kohteen nimi","kivi"),
      "tradetype_id" => __("Ilmoitustyyppi 1=myynti, 2=vuokraus, 3=myynti tai vuokraus","kivi"),
      "assignmentrent_rent" => __("Kuukausivuokra","kivi"),
      "external_user_id" => __("Ulkopuolisen järjestelmän käyttäjä-ID","kivi"),
      "price" => __("Myyntihinta","kivi"),
      "debt" => __("Velkaosuus","kivi"),
      "realty_unique_no" => __("Kohdenumero","kivi"),
      "realty_vi_presentation" => __("Virtuaaliesittely","kivi"),
      "unencumbered_price" => __("Velaton hinta","kivi"),
      "vi_pre_url" => __("Virtuaaliesittelyn osoite","kivi"),
      "realtyrealtyoption" => __("Moniarvoiset kohdetietovalinnat","kivi"),
      "realtyoption" => __("Arvon nimi","kivi"),
      "realtyoption_id" => __("Arvon nimi","kivi"),
      "realtyoptiongroup_id" => __("Ryhmä johon arvo kuuluu / Otsikko arvolle","kivi"),
      "kivipresentation" => __("Esittelyajat","kivi"),
      "presentation_date" => __("Esittelyn päivämäärä","kivi"),
      "presentation_end" => __("Esittelyn loppumisaika","kivi"),
      "presentation_info" => __("Esittelyn lisätiedot","kivi"),
      "presentation_start" => __("Esittelyn alkamisaika","kivi"),
      "removed_flag" => __("Esittely poistettu","kivi"),
      "igglo_customer_id" => __("Igglo","kivi"),
      "igglo_silentsale_realty_flag" => __("Igglo","kivi"),
      "igglo_ad_id" => __("Igglo","kivi"),
      "assignmentsale_free_other" => __("Vapautuminen","kivi"),
      "assignmentsale_free_type_name" =>  __("Vapautuminen","kivi"),
      "assignmentsale_free_date"=>  __("Vapautuu","kivi")
    );

    return ( array_key_exists($meta_field, $arr) ) ? $arr[$meta_field] : false;
  }

  /**
   * Filter single view property values for cleaner output.
   *
   * @return value. If nothing changes, return param1 "value"
   * @since    1.0.3
   */
	function filter_viewable_values( $value, $label, $properties ) {

		if( empty($value) ){
			return $value;
		}

		// change float value to integer if .00
		if( is_numeric($value) && fmod($value, 1) === 0.00 ){
			$value = intval($value);
		}

		// replace "m2" with square character
		$value = str_replace('m2', 'm²', $value);

		// only check for single properties
		if( count($properties) == 1 && isset($properties[0]) ){

			// change first character to uppercase
			$value = ucfirst ( $value );

			$kivi_property = $properties[0];

			// change listed name values to int
			$to_integer = array(
				'_floor',
				'_floors',
			);
			if( in_array($kivi_property->name, $to_integer) ){
				return intval($value);
			}

			// format values to prices for listed names
			$to_price = array(
				'_unencumbered_price',
				'_price',
				'_debt',
				'_assignmentrent_rent',
				'_chargesmaint2_month',
				'_charges_condominium_total_mo',
				'_charges_finance_base_month',
				'_charges_maint_base_month',
                '_charges_realtytax',
                '_charges_heating',
                '_charges_road',
                '_rc_lot_rent',
                '_highest_bid',
			);
			if( in_array($kivi_property->name, $to_price) ){
				$value = +$value;
				if( is_numeric($value) ){
                    if( is_float($value) || intval($value) < 1000 ){
                        $price = number_format($value, 2, ',', ' '); // to 2 decimal price string with thousand separators
                    }
                    elseif( is_float($value) && intval($value) > 999 ){
                        $price = number_format($value, 2, ',', ' '); // with thousands separated and two decimals
                    }
                    elseif( intval($value) > 999 ){
                        $price = number_format($value, 0, ',', ' '); // with thousands separated
                    }
                    else{
                        return $value;
                    }
                    return $price." €";
                }
			}

			// custom solution for lot size
			if( '_rc_lot_area_m2' == $kivi_property->name ){
				$lot_measure = get_post_meta( $kivi_property->post_id, '_lot_measuretype_id', true );
				if( !empty($lot_measure) ){ // could be: "", "m2" or "ha"
					return str_replace('.', ',', $value). " " .str_replace('m2', 'm²', $lot_measure);
				}
			}

			// check for property name ending "_m2"
			if( stripos(strrev($kivi_property->name), "2m_") === 0 ){
				return floatval($value) . " m²";
			}

		}

		return $value;
	}

	/**
	* Hide presentations in the past
	*/
	function filter_presentation_date( $value, $label, $properties ) {

		if('Esittelyt' == $label){

			$date_obj = null;

			// $value starts with date(d.m.Y) = 10 chars
			$date = substr(trim($value), 0, 10);
			$date = trim($date);
			if( !empty($date)){
				$date_obj = DateTime::createFromFormat('d.m.Y H:i', $date." 23:59" ); // use late time of date in compare
			}

			if( !empty($date_obj) ){
				$cur_obj = new DateTime();
				if($date_obj < $cur_obj){
					return "";
				}
			}
		}

		return $value;
	}

	function filter_format_date( $value, $label ) {

		if( 'Taloyhtiön (Tontin) Vuokrasopimus päättyy' == $label || 'Vapautuu' == $label ) {
			if( !empty($value) ){
				$date = new DateTime( $value );
				$value = $date->format('d.m.Y');
			}
		}

        return $value;
	}

	function filter_charges_eheating( $value, $label, $properties ){

        if( empty($value) ){
            return $value;
        }

		// only check for single properties
		if( count($properties) == 1 && isset($properties[0]) ){

			$kivi_property = $properties[0];

			if( '_charges_eheating' == $kivi_property->name ){
				$value = $value . " € / " . __( "kk", "kivi" );
			}
		}

		return $value;
    }
	
	function filter_charges_parkingspace( $value, $label, $properties ){

        if( empty($value) ){
            return $value;
        }

		// only check for single properties
		if( count($properties) == 1 && isset($properties[0]) ){
			$kivi_property = $properties[0];
			if( '_charges_parkingspace' == $kivi_property->name ){
				$value .= " €";
				$parkingcharge_type_id = get_post_meta( $kivi_property->post_id, '_parkingcharge_type_id', true ); // "kk" | "v"
				if( $parkingcharge_type_id ) {
					$value .= " / ".$parkingcharge_type_id;
				}
			}
		}
		return $value;
    }
	
    function filter_bid_url( $value, $label ){

        if( empty($value) ){
            return $value;
        }
        if('Linkki kohteen seurantasivulle' == $label  && filter_var($value, FILTER_VALIDATE_URL) ){
            $value = lcfirst( $value );
            $value = '<a href="' . $value . '" target="_blank" rel="noopener noreferrer" class="kivi-bids-url">Seuraa kohteen tarjouskauppaa »</a>';
        }

        return $value;

    }
}
