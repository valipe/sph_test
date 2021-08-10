<?php
namespace Drupal\product\Plugin\Block;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Provides a block with QR code.
 *
 * @Block(
 *   id = "product_block",
 *   admin_label = @Translation("Product QR Block"),
 * )
 */
class ProductQRBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * RouteMatch used to get parameter Node.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;
  /**
   * Constructs a Drupal\product\Plugin\block\ProductQRBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    // Set cache contexts.
    $cachableMetadata = new CacheableMetadata();
    $cachableMetadata->setCacheContexts(['route']);

    // Displaying QR Code for only Product Pages
    if (($node = $this->routeMatch->getParameter('node'))
      && $node instanceof NodeInterface && $node->bundle() === 'product'
      && class_exists('TCPDF2DBarcode')) {
      // Add cacheable dependency.
      $cachableMetadata->addCacheableDependency($node);
      $link = $node->field_product_purchase_link->uri;

      // Product QR Code function
      $qrCodeObj = new \TCPDF2DBarcode($link, 'QRCODE,H');
      $build = [
        '#type' => 'inline_template',
        '#template' => $qrCodeObj->getBarcodeHTML(4, 4, 'black'),
      ];
    }
    $cachableMetadata->applyTo($build);
    return $build;
  }

}