import puppeteer from 'puppeteer';
import {
  getSecret,
  loginAsSuperAdmin,
  searchForUser,
  searchForClient,
  checkOrganisations,
  checkSubmissions,
  checkAnalytics,
  checkServiceHealthAdmin
} from './../utility/Utility.js';

const url = process.env.ADMIN_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;

const runSmoke = async () => {
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: ['--no-sandbox', '--headless']
    });
  const page = await browser.newPage();

  try {
    const { user, password, client } = await getSecret(environment, endpoint);
    await loginAsSuperAdmin(page, url, user, password);
    await searchForUser(page, user);
    await searchForClient(page, client);
    await checkOrganisations(page);
    await checkSubmissions(page);
    await checkAnalytics(page);
    await checkServiceHealthAdmin(page, url);
  } catch (error) {
    console.error('Smoke tests failed:', error);
  } finally {
    await browser.close();
  }
};

runSmoke();
