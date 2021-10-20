#Adding Report Sections

Complete the Deputy Report service comprises a number of sections of a larger form that deputies use to report on how they have carried out their deputyship details over a 12-month period. In code, this is modelled on the `Report` entity with each subsection defined as separate entities and linked to the report in one-to-one relationships.

In order to add a new section to the report carry out the following steps:

##API
* Decide on a descriptive name for the report section and create a new entity class in `api/src/entity/`
* Add the relevant annotations to signal to Doctrine that the entity class is an entity and should have a corresponding database table:

```phpt
/**
 * @ORM\Table(name="lifestyle")
 * @ORM\Entity
 */
class Lifestyle
{}
```

* Map the questions included in the form section to properties on the entity class including any required Doctrine notations related to the expected response types. For example, a question asking if a client undertakes social activities could be mapped as follows:

```phpt
    /**
     * @var string yes|no|null
     *
     * @JMS\Type("string")
     * @JMS\Groups({"lifestyle"})
     * @ORM\Column( name="does_client_undertake_social_activities", type="string", length=4, nullable=true)
     */
    private $doesClientUndertakeSocialActivities;
```

* When all questions are mapped to properties, exec in to the api docker container and generate a new migration file using the Symfony console:

```
> docker-compose exec api sh

> php app/console doctrine:migrations:diff
```

* Check the contents of the migration file generated to ensure it captures the required database changes and amend if necessary. Rename the migration file following the naming convention in `api/src/Migrations` and, still inside the api docker container, run the migration:

```
> php app/console doctrine:migrations:migrate
```

* Inspect the database to ensure the new tables and changes are as expected

* Add a new `SECTION_` constant to the Report entity with the name of the section and add to `Report::getSectionsSettings()` assigning which report types the section should appear in

* Add a getter for the section state to `ReportStatusService` including relevant JMS tags. The name of the getter function will be transformed as the state name when returned from the API:

```phpt
    /**
     * @JMS\VirtualProperty
     * @JMS\Type("array")
     * @JMS\Groups({"status", "lifestyle-state"})
     *
     * @return array
     */
    public function getLifestyleState()
    {
        ...
    }
```

becomes:

```phpt
    [lifestyle_state] => [
        [state] => done
        [nOfRecords] => 0
    ]
```

##Client

* Create a representation of the `api` report section entity in `client` including all the properties. Ensure the entity includes the `HasReportTrait` added to it:

```phpt
class Lifestyle
{
    use HasReportTrait;
    ...
}
```

* Add the newly created entity as a property to the `Report` entity:

```phpt
    /**
     * @JMS\Type("App\Entity\Report\Lifestyle")
     *
     * @var Lifestyle|null
     */
    private $lifestyle;
```

* Add a section status property to Report/Status following the same naming convention used in API `ReportStatusService`

* If there are going to be multiple instances of the report section entity associated with the report then add a trait in Entity/Report/Traits to store the entities in an ArrayCollection and define any functions that will interact with the entities collection

* Create a new form class and map the report section entity properties to the form questions as required (see examples in `client/src/Form/Report`)

* Add a new section to the report overview template (`App/Report/Report/overview.html.twig`) by including a new `App/Report/Report/_subsection.html.twig` partial. If required for NDR reports also include in `App/Ndr/Ndr/overview.html.twig`

* Create a corresponding controller and implement the form logic as required using the newly created form type

* Add an entry for the new section \to `ReportSectionsLinkService` and then include the `_nextprevious.html.twig` partial on the first and last page of the section
