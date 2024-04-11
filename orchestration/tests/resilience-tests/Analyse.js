import {
    analyseUserExperience
} from './../utility/DataAnalysis.js';

const taskTimingsFilePath = process.env.TASK_TIMINGS_LOG

analyseUserExperience(taskTimingsFilePath);
