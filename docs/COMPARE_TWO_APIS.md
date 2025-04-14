## Compare two APIs

Under some circumstances we may need to write some new APIs and we may want to check that they
return the expected results with live like data.

We have developed a methodology to do this. We can use the following command (giving the old route and the new route):

```
php app/console digideps:api:api-comparison "/client/{client_id}" "/v2/client/{client_id}"
```

To accomplish this, we pass an interable comparer selector and select which one to use based on the routes provided.

As such if you want to set up some new routes to compare then you will need to create a new concrete class in
ResponseComparison and add a SQL statement for the selection of IDs to test and what you intend to validate on.

This can then be run overnight in preproduction to give confidence in our API changes.
