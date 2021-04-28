## How to test a section

Create a new directory for each new section on the report.

Test the happy path navigation through the section, including editing inputs from the summary page. Test the contents on the summary page.

Do not test error handling on each input, this is to be tested by unit tests.

### Directory structure

If the section is the same for all deputy types then only create one test file, e.g:

```$xslt
bank-accounts/
    bank-accounts.feature
```

If the section differs between lay and organisation based deputies, create two test files, e.g:

```$xslt
bank-accounts/
    bank-accounts.lay.feature
    bank-accounts.org.feature
```

If the section differs between lay, PA, and professional deputies, create three test files, e.g:

```$xslt
bank-accounts/
    bank-accounts.lay.feature
    bank-accounts.pa.feature
    bank-accounts.prof.feature
```
