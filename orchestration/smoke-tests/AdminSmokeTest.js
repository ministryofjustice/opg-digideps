const puppeteer = require('puppeteer');
const { SecretsManager, SecretsManagerClient, GetSecretValueCommand } = require('@aws-sdk/client-secrets-manager');

const url = process.env.ADMIN_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;
let smcParams

const checkTextInElement = (expectedText, actualText) => {
    if (actualText.includes(expectedText)) {
      console.log(`The element contains "${expectedText}".`);
    } else {
      console.error(`The element does not contain "${expectedText}". Actual text: ${actualText}`);
    }
  };

const getSecret = async () => {
  console.log('===== Pre-Step: Get Secret Values from '+environment+' =====');
  const secretName = environment + '/smoke-test-variables';
  const input = {
    "SecretId": secretName
  };

  if (environment === 'local') {
    smcParams = {
      region: 'eu-west-1',
      endpoint: endpoint,
      credentials: {
        accessKeyId: 'test',
        secretAccessKey: 'test'
      }
    };
  } else {
    smcParams = {region: 'eu-west-1'};
  }
  console.log(smcParams);
  const secretsManagerClient = new SecretsManagerClient(smcParams)
  const secretValue = await secretsManagerClient.send(new GetSecretValueCommand(input));
  const secretData = JSON.parse(secretValue.SecretString);
  const { user, password, client } = secretData;
  console.log('User:', user);

  return { user, password, client };
};

const loginAsSuperAdmin = async (page, user, password) => {
    console.log('===== Step 1: Logging in to app as Super Admin =====');
    console.log(user);
    await page.goto(url + '/login')
    await page.type('#login_email', user);
    await page.type('#login_password', password);
    await Promise.all([
        page.waitForNavigation(),
        page.click('#login_login')
      ]);

    const currentURL = page.url();

    if (currentURL === url + '/admin/') {
      console.log('Successfully navigated to the login page.');
    } else {
      console.error('Failed to navigate to the login page. Current URL:', currentURL);
    }
  };

const searchForUser = async (page, user) => {
    console.log('===== Step 2: Try searching for a user =====');
    await page.type('#admin_q', user);
    await Promise.all([
        page.waitForNavigation(),
        page.click('#admin_search')
      ]);
    await page.waitForSelector('.behat-region-users');
    const searchText = await page.$eval('.behat-region-users', element => {
      const childElement = element.querySelector('p.govuk-body');
      return childElement ? childElement.textContent.trim() : '';
    });
    checkTextInElement('Found 1 user', searchText);
};

const searchForClient = async (page, clientToFind) => {
    console.log('===== Step 3: Try searching for a client =====');
    await page.waitForSelector('.behat-link-admin-client-search');
    await page.click('.behat-link-admin-client-search')
    await page.type('#search_clients_q', clientToFind);
    await Promise.all([
        page.waitForNavigation(),
        page.click('#search_clients_search')
      ]);
    await page.waitForSelector('.behat-region-client-search-count');
    const clientText = await page.$eval('.behat-region-client-search-count', element => element.textContent.trim());
    checkTextInElement('Found 1 clients', clientText);
};

const checkServiceHealth = async (page) => {
    console.log('===== Step 4: Check service health =====');
    await page.goto(url + '/health-check/service')
    const healthText = await page.$eval('body', body => body.textContent.replace(/\s+/g, '').trim());
    checkTextInElement('Api:OK', healthText);
    checkTextInElement('Redis:OK', healthText);
};

const runSmoke = async () => {
  const browser = await puppeteer.launch(
    {
      executablePath: '/usr/bin/chromium-browser',
      args: ['--no-sandbox', '--headless']
    });
  const page = await browser.newPage();

  try {
    const { user, password, client } = await getSecret();
    await loginAsSuperAdmin(page, user, password);
    await searchForUser(page, user);
    await searchForClient(page, client);
    await checkServiceHealth(page);
  } catch (error) {
    console.error('Smoke tests failed:', error);
  } finally {
    await browser.close();
  }
};

runSmoke();
