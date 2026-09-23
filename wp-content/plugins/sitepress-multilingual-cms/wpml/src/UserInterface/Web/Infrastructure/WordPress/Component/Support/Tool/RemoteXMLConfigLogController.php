<?php

namespace WPML\UserInterface\Web\Infrastructure\WordPress\Component\Support\Tool;

use WPML\UserInterface\Web\Core\SharedKernel\Config\PageRenderInterface;
use WPML_Config_Update_Log;


class RemoteXMLConfigLogController implements PageRenderInterface {

  const WIDTH_CLASS = 'wpml:max-w-5xl';


  public function render() {
    $entries = $this->collectEntries();
    $columns = $this->collectColumns( $entries );
    ?>
      <h1 class="wpml:text-2xl wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
        <?php esc_html_e( 'Remote XML config log', 'wpml' ); ?>
      </h1>
      <p class="wpml:text-sm wpml:text-gray-500 wpml:mb-6">
        <?php
        printf(
          /* translators: Description of a Support tool on WPML → Support. %1$s: the file name "wpml-config.xml", shown in a code box, %2$s: a link reading "Settings → Custom XML Configuration". */
          esc_html__( "Tracks the %1\$s files WPML has read from themes and plugins, including remote configurations fetched on update. Use this log when a custom config file from a theme or plugin doesn't seem to apply: support uses it to confirm WPML actually saw and parsed the file. Read the description in %2\$s for context on what these files do.", 'wpml' ),
          '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wpml-config.xml</code>',
          '<a href="' . esc_url( admin_url( 'admin.php?page=tm/menu/settings&section=custom-xml' ) ) . '" class="wpml:text-blue wpml:hover:underline">' . esc_html__( 'Settings → Custom XML Configuration', 'wpml' ) . '</a>'
        );
        ?>
      </p>

      <div class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:overflow-hidden wpml:mb-4">
        <?php if ( empty( $entries ) ) : ?>
          <p class="wpml:px-5 wpml:py-8 wpml:text-center wpml:text-gray-400 wpml:italic wpml:text-sm">
            <?php esc_html_e( 'The remote XML config log is empty.', 'wpml' ); ?>
          </p>
        <?php else : ?>
          <table class="wpml:w-full wpml:text-sm">
            <thead class="wpml:bg-gray-50 wpml:text-[11px] wpml:font-semibold wpml:tracking-wide wpml:text-gray-500 wpml:uppercase wpml:border-b wpml:border-gray-100">
              <tr>
                <th class="wpml:text-left wpml:px-4 wpml:py-2 wpml:font-semibold wpml:whitespace-nowrap"><?php /* translators: Column heading in a log table on WPML → Support: when the entry was written. */ esc_html_e( 'Time', 'wpml' ); ?></th>
                <?php foreach ( $columns as $column ) : ?>
                  <th class="wpml:text-left wpml:px-4 wpml:py-2 wpml:font-semibold"><?php echo esc_html( $column ); ?></th>
                <?php endforeach; ?>
              </tr>
            </thead>
            <tbody class="wpml:divide-y wpml:divide-gray-100 wpml:text-xs">
              <?php foreach ( $entries as $timestamp => $entry ) : ?>
                <tr>
                  <td class="wpml:px-4 wpml:py-2 wpml:text-gray-600 wpml:whitespace-nowrap wpml:font-mono"><?php echo esc_html( $this->formatTimestamp( $timestamp ) ); ?></td>
                  <?php foreach ( $columns as $column ) : ?>
                    <td class="wpml:px-4 wpml:py-2 wpml:text-gray-700"><?php echo esc_html( $this->cellValue( $entry, $column ) ); ?></td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <div class="wpml:bg-white wpml:border wpml:border-gray-200 wpml:rounded-md wpml:p-4">
        <h2 class="wpml:text-sm wpml:font-semibold wpml:text-gray-900 wpml:mb-1">
          <?php esc_html_e( 'Clear the log', 'wpml' ); ?>
        </h2>
        <p class="wpml:text-xs wpml:text-gray-500 wpml:mb-3">
          <?php
          printf(
            /* translators: %s is the literal `wpml-config.xml`. */
            esc_html__( "Removes the entries above. WPML will rebuild the log on the next config-file scan — useful when you've renamed or removed a %s file and want WPML to scan again from scratch.", 'wpml' ),
            '<code class="wpml:text-[11px] wpml:bg-gray-100 wpml:px-1 wpml:rounded">wpml-config.xml</code>'
          );
          ?>
        </p>
        <button type="button" disabled
          title="<?php esc_attr_e( 'Wires up in a phase-2 follow-up — needs a nonce-guarded wp-ajax wrapper around the legacy clear action.', 'wpml' ); ?>"
          class="wpml-button base-btn wpml-button--outlined wpml:text-sm wpml:disabled:opacity-50 wpml:disabled:cursor-not-allowed">
          <?php /* translators: Button label on WPML → Support that deletes the log. Verb phrase, imperative. */ esc_html_e( 'Remove log', 'wpml' ); ?>
        </button>
      </div>
      <?php
  }


  private function collectEntries(): array {
    if ( ! class_exists( WPML_Config_Update_Log::class ) ) {
      return array();
    }

    $log     = new WPML_Config_Update_Log();
    $entries = $log->get();
    if ( ! is_array( $entries ) ) {
      return array();
    }
    krsort( $entries );
    return $entries;
  }


  private function collectColumns( array $entries ): array {
    $columns = array();
    foreach ( $entries as $entry ) {
      if ( is_array( $entry ) ) {
        $columns = array_merge( $columns, array_keys( $entry ) );
      }
    }
    return array_values( array_unique( $columns ) );
  }


  private function cellValue( $entry, string $column ): string {
    if ( ! is_array( $entry ) || ! isset( $entry[ $column ] ) ) {
      return '';
    }
    $value = $entry[ $column ];
    if ( is_scalar( $value ) || ( is_object( $value ) && method_exists( $value, '__toString' ) ) ) {
      return (string) $value;
    }
    return (string) wp_json_encode( $value );
  }


  private function formatTimestamp( string $raw ): string {
    if ( $raw === '' ) {
      return '';
    }
    $seconds = is_numeric( $raw ) ? (int) $raw : 0;
    if ( $seconds <= 0 ) {
      return $raw;
    }
    return (string) wp_date( 'Y-m-d H:i:s', $seconds );
  }


}
