import { expect, Page } from "@playwright/test";

export default abstract class DocumentsFileListPage {
  private fileNamesSelector: string = "dl.behat-region-document-list > div.govuk-summary-list__row > dt.govuk-summary-list__value"

  public constructor(protected page: Page, protected reportId: number) {}

  abstract goto(): Promise<void>

  async removeFiles(...fileNames: string[]) {
    for (const fileName of fileNames) {
      await this.page.getByRole("link", { name: `Remove  ${fileName}` }).click()
    }
  }

  async expectFileNames(...expectedFileNames: string[]) {
    const fileNameElements = await this.page.locator(this.fileNamesSelector).all()

    const fileNames: string[] = []
    for (const fileNameElement of fileNameElements) {
      const text = await fileNameElement.textContent()
      if (text !== null) {
        fileNames.push(text.trim())
      }
    }

    expect(fileNames.length).toBe(expectedFileNames.length)
    expect(fileNames).toEqual(expect.arrayContaining(expectedFileNames))
  }
}
