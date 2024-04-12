import { FisClient, StartExperimentCommand } from "@aws-sdk/client-fis"; // ES Modules import
const experimentTemplateId = process.env.STOP_FRONTEND_TASK_XID;
const clientToken = Date.now().toString();
let config = {region: 'eu-west-1'}
const client = new FisClient(config);
const input = {
  clientToken: clientToken,
  experimentTemplateId: experimentTemplateId,
  experimentOptions: {
    actionsMode: "run-all",
  }
};
const command = new StartExperimentCommand(input);
const response = await client.send(command);
if (response.experiment.state.status == 'initiating') {
    console.log('\n\n===== EXPERIMENT STARTED ======\n\n');
}
