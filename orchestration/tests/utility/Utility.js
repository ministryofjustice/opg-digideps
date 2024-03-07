import { SecretsManager, SecretsManagerClient, GetSecretValueCommand } from '@aws-sdk/client-secrets-manager';

const checkTextInElement = (expectedText, actualText) => {
    if (actualText.includes(expectedText)) {
      console.log(`The element contains "${expectedText}".`);
    } else {
      console.error(`The element does not contain "${expectedText}". Actual text: ${actualText}`);
    }
  };

const checkValueGreaterThanZero = async (page, selector) => {
  await page.waitForSelector(selector);
  const textContent = await page.$eval(selector, element => element.textContent.trim());
  const value = parseInt(textContent, 10);
  if (value > 0) {
    console.log(`The value ${value} is greater than 0.`);
  } else {
    console.log(`The value ${value} is not greater than 0.`);
  }
};

const getSecret = async (environment, endpoint) => {
  console.log('===== Pre-Step: Get Secret Values from '+environment+' =====');
  let smcParams;
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
  const secretsManagerClient = new SecretsManagerClient(smcParams)
  const secretValue = await secretsManagerClient.send(new GetSecretValueCommand(input));
  const secretData = JSON.parse(secretValue.SecretString);
  const { user, password, client } = secretData;

  return { user, password, client };
};

const loginAsSuperAdmin = async (page, url, user, password) => {
    console.log('===== Logging in to app as smoke user =====');
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
    console.log('===== Try searching for a user =====');
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
    console.log('===== Try searching for a client =====');
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

const checkOrganisations = async (page) => {
  console.log('===== Check organisations show up =====');
  await page.click('.behat-link-admin-organisations')
  await page.waitForSelector('.govuk-table__body');
  const rowCount = await page.$$eval('.govuk-table__body tr', rows => rows.length);
  if (rowCount > 0) {
    console.log(`Found ${rowCount} rows in the table.`);
  } else {
    console.log('No rows found in the table.');
  }
};

const checkSubmissions = async (page) => {
  console.log('===== Check submissions show up =====');
  await page.click('.behat-link-admin-documents');
  await page.waitForSelector('.behat-link-tab-archived');
      await Promise.all([
        page.waitForNavigation(),
        page.click('.behat-link-tab-archived')
      ]);
  const rowCount = await page.$$eval('.govuk-table__body tr', rows => rows.length);
  if (rowCount > 0) {
    console.log(`Found ${rowCount} rows in the table.`);
  } else {
    console.log('No rows found in the table.');
  }
};

const checkAnalytics = async (page) => {
  console.log('===== Check Analytics Page =====');
  await Promise.all([
    page.waitForNavigation(), // This line will wait for navigation to complete
    page.click('.behat-link-admin-analytics'),
  ]);
  const textContent = await page.$eval('.govuk-heading-xl[aria-labelledby="metric-registeredDeputies-total-label"]', element => element.textContent.trim());
  const value = parseInt(textContent, 10);
  if (value > 0) {
    console.log(`The value ${value} is greater than 0.`);
  } else {
    console.log(`The value ${value} is not greater than 0.`);
  }
};

const checkServiceHealthAdmin = async (page, url) => {
    console.log('===== Check service health admin =====');
    await page.goto(url + '/health-check/service');
    const healthText = await page.$eval('body', body => body.textContent.replace(/\s+/g, '').trim());
    checkTextInElement('Api:OK', healthText);
    checkTextInElement('Redis:OK', healthText);
};

const checkServiceHealthFront = async (page, url) => {
    console.log('===== Check service health frontend =====');
    await page.goto(url + '/health-check/service')
    const healthText = await page.$eval('body', body => body.textContent.replace(/\s+/g, '').trim());
    checkTextInElement('Api:OK', healthText);
    checkTextInElement('Redis:OK', healthText);
    checkTextInElement('ClamAV:OK', healthText);
    checkTextInElement('htmlToPdf:OK', healthText);
};

export {
    getSecret,
    loginAsSuperAdmin,
    searchForUser,
    searchForClient,
    checkServiceHealthAdmin,
    checkServiceHealthFront,
    checkOrganisations,
    checkSubmissions,
    checkAnalytics
};
