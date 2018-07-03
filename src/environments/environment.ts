// The file contents for the current environment will overwrite these during build.
// The build system defaults to the dev environment which uses `environment.ts`, but if you do
// `ng build --env=prod` then `environment.prod.ts` will be used instead.
// The list of which env maps to which file can be found in `.angular-cli.json`.

export const environment = {
  production: false,
  sso: '//192.168.1.68/SSO/',
  api: '//192.168.1.68/RRHHSystem/api/',
  debug: true
};
