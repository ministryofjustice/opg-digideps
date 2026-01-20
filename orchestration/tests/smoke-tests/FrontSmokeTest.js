import puppeteer from 'puppeteer';

const url = process.env.FRONT_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;

import {
  getSecret,
  loginAsUser,
  checkReportSectionsVisible,
  updateUserDetails,
  logOutUser,
  checkServiceHealthFront,
  openPageWithRetries
} from './../utility/Utility.js';

const runSmoke = async () => {
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: ['--no-sandbox', '--headless'],
      protocolTimeout: 30000
    });

  const page = await openPageWithRetries(browser);
  await page.setRequestInterception(true);

  page.on('request', req => {
    const url = req.url();

    if (
      url.includes('googletagmanager.com') ||
      url.includes('google-analytics.com')
    ) {
      return req.abort();
    }

    req.continue();
  });
  try {
    const { admin_user, admin_password, client, deputy_user, deputy_password } = await getSecret(environment, endpoint);
    const user = deputy_user;
    const password = deputy_password;
    await loginAsUser(page, url, user, password, 'courtorder/');
    await checkReportSectionsVisible(page);
    await updateUserDetails(page, '#profile_firstname', '#profile_save')
    await logOutUser(page, url)
    await checkServiceHealthFront(page, url);
  } catch (error) {
    console.error('Smoke tests failed:', error);
    process.exit(1);
  } finally {
    await browser.close();
  }
};

runSmoke();
