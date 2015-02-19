#Build 2

  * Recreate db
    
        echo "DROP SCHEMA IF EXISTS public cascade; CREATE SCHEMA IF NOT EXISTS public;" | sudo -u postgres psql dd_api
        phing db

  * Manual tests

        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"firstname":"Elvis","lastname":"Ciotti","email":"elvis.ciotti@digital.justice.gov.uk"}'  http://digideps-api.local/user
        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X GET  http://digideps-api.local/user
        curl -v -H "Accept: application/json" -H "Content-type: application/json" -X POST -d '{"name":"Paul","last_name":"Oforduru","email":"paul@digital.justice.gov.uk"}'  http://digideps-api.local/user
  
  * Rest

        See RestInputOuputFormatter class and config from config.yml
        
        Useful links

          * Serialized entities annotations here http://jmsyst.com/libs/serializer/master/reference/annotations
          * http://symfony.com/doc/current/cookbook/service_container/event_listener.html
          * http://symfony.com/doc/current/components/http_kernel/introduction.html



