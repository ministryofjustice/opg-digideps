import ConditionalFieldRevealer from '../modules/ConditionalFieldRevealer'

window.addEventListener('DOMContentLoaded', (event) => {
  // We need to wait for gov.uk DS to apply its JS/CSS after DOM has loaded
  window.setTimeout(timeOutCallback, 200)
})

const timeOutCallback = () => {
  ConditionalFieldRevealer.init()
}
