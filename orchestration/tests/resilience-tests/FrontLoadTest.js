import { Cluster } from 'puppeteer-cluster';
import puppeteer from 'puppeteer';
import fs from 'fs';
import {
  getSecret,
  loginAsUser,
  checkReportSectionsVisible,
  updateUserDetailsConcurrent,
  logOutUser,
  checkServiceHealthFront
} from './../utility/Utility.js';

const url = process.env.FRONT_URL;
const environment = process.env.ENVIRONMENT;
const endpoint = process.env.ENDPOINT;
const taskTimingsFilePath = process.env.TASK_TIMINGS_LOG;
const taskErrorsFilePath = process.env.TASK_ERROR_LOG;

// Define your task
async function measureElapsedTime(page, action, csvRow, timeoutValue) {
    const startTime = Date.now();
    await action(page);
    const endTime = Date.now();
    const elapsedTime = endTime - startTime;
    csvRow += `${elapsedTime},`;
    console.log('elapsedTime: '+elapsedTime);
    await new Promise(resolve => setTimeout(resolve, timeoutValue));
    return csvRow;
}

async function task({ page, data }) {
    const { url, user, password } = data;
    const timeoutValue = Math.floor(Math.random() * (2000 - 499)) + 500;
    console.log('timeoutvalue: '+timeoutValue);
    await new Promise(resolve => setTimeout(resolve, timeoutValue));
    let csvRow = `${Date.now()},`;

    try {
        csvRow = await measureElapsedTime(page, loginAsUser.bind(null, page, url, user, password, 'lay'), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, checkReportSectionsVisible.bind(null, page), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, updateUserDetailsConcurrent.bind(null, page, '#profile_firstname', '#profile_save'), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, logOutUser.bind(null, page, url), csvRow, timeoutValue);
        csvRow = await measureElapsedTime(page, checkServiceHealthFront.bind(null, page, url), csvRow, timeoutValue);

        fs.appendFileSync(taskTimingsFilePath, csvRow + '\n');
    } catch (error) {
        csvRow = `${Date.now()},10000,10000,10000,10000,10000,`
        fs.appendFileSync(taskTimingsFilePath, csvRow + '\n');
        console.error('Error in task:', error);
    } finally {
        await page.close();
    }
}

// Create a cluster
(async () => {
    fs.writeFileSync(taskTimingsFilePath, 'timestamp,login,check_report,update_name,logout,check_health\n');
    fs.writeFileSync(taskErrorsFilePath, 'time_hours_minutes,error_message\n');
    const cluster = await Cluster.launch({
        concurrency: Cluster.CONCURRENCY_CONTEXT,
        maxConcurrency: 3, // Number of threads you want to run concurrently
        puppeteer,
        puppeteerOptions: {
            executablePath: '/usr/bin/chromium-browser',
            timeout: 5000000,
            args: ['--no-sandbox', '--headless']
        },
    });
    console.log(url);
    const { _a, _b, _c, deputy_user, deputy_password } = await getSecret(environment, endpoint);
    const user = deputy_user;
    const password = deputy_password;

    // Start the timer
    const startTime = Date.now();
    const duration = 5 * 60 * 1000; // 5 minutes in milliseconds

    // Loop until the specified duration
    while (Date.now() - startTime < duration) {
        // Define tasks
        for (let i = 0; i < 3; i++) { // Number of threads
            cluster.queue({ url, user, password }, task);
        }
        await cluster.idle(); // Wait for all tasks to finish
        await new Promise(resolve => setTimeout(resolve, 2000)); // Wait for 2 seconds before starting the next iteration
    }

    await cluster.close(); // Close the cluster after the specified duration
})();
