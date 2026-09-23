const { execFileSync } = require('child_process');
const { readFileSync, unlinkSync } = require('fs');
const path = require('path');
const webpackConfig = require('../../webpack.config');
const domain = "wpml";

const files = Object.keys( webpackConfig.entry );
const configScriptsPath = path.resolve( __dirname, '../../src/config-scripts.php' );
const configScripts = JSON.parse(
  execFileSync(
    'php',
    [
      '-r',
      'echo json_encode(require $argv[1], JSON_THROW_ON_ERROR);',
      configScriptsPath,
    ],
    { encoding: 'utf8' }
  )
);

const handlesByFileName = Object.entries( configScripts ).reduce(
  ( handles, [ handle, config ] ) => {
    if ( config.src ) {
      handles[ path.basename( config.src ) ] = handle;
    }

    return handles;
  },
  {}
);

const hasTranslatableEntries = ( filePath ) => {
  const pot = readFileSync( filePath, 'utf8' );

  // The first msgid is the POT header. Any additional msgid represents a
  // translatable string extracted from the JavaScript bundle.
  return ( pot.match( /^msgid\s/gm ) || [] ).length > 1;
};

let hasErrors = false;

files.forEach((file) => {
  const fileName = file + '.js'
  const fallbackHandle = file.startsWith( 'wpml-' ) ? file : `wpml-${file}`;
  const handle = handlesByFileName[ fileName ] || fallbackHandle;
  const output = `languages/pot/${domain}-${handle}.pot`;
  try {
    console.log(`Generating ${output} for the ${handle} script handle`);
    execFileSync(
      'wp',
      [
        'i18n',
        'make-pot',
        './public/js',
        output,
        `--include=${fileName}`,
        '--no-location',
        `--domain=${domain}`,
        '--skip-php',
      ],
      { stdio: 'inherit' }
    );

    if ( ! hasTranslatableEntries( output ) ) {
      unlinkSync( output );
      console.log(`Skipping empty POT file: ${output}`);
    }
  } catch (error) {
    hasErrors = true;
    console.error(`Error executing command for ${fileName}:`, error);
  }
});

if ( hasErrors ) {
  process.exitCode = 1;
}
