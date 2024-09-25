import puppeteer from 'puppeteer';
import {
  getSecret,
  loginAsUser,
  searchForUser,
  searchForClient,
  checkOrganisations,
  checkSubmissions,
  checkAnalytics,
  updateUserDetails,
  logOutUser,
  checkServiceHealthAdmin,
  openPageWithRetries
} from './../utility/Utility.js';

const url = process.env.ADMIN_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;

const runSmoke = async () => {
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: ['--no-sandbox', '--headless'],
      protocolTimeout: 30000
    });
  const version = await browser.version();
  console.log(`Running Chromium version: ${version}`);

  const page = await openPageWithRetries(browser);

  try {
    const { admin_user, admin_password, client, deputy_user, deputy_password } = await getSecret(environment, endpoint);
    const user = admin_user;
    const password = admin_password;
    await loginAsUser(page, url, user, password, 'admin');
    await searchForUser(page, user);
    await searchForClient(page, client);
    await checkOrganisations(page);
    await checkSubmissions(page);
    await checkAnalytics(page);
    await updateUserDetails(page, '#user_details_firstname', '#user_details_save');
    await logOutUser(page, url)
    await checkServiceHealthAdmin(page, url);
  } catch (error) {
    console.error('Smoke tests failed:', error);
    process.exit(1);
  } finally {
    await browser.close();
  }
};

runSmoke();
