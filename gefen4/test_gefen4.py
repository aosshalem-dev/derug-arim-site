"""
Test gefen4 v2: scrollbar, 20-cities toggle, colors.
"""
import asyncio
from playwright.async_api import async_playwright

URL = "https://gnostocracy.com/ranking/gefen4/"
OUT = "/Users/zvishalem/Library/CloudStorage/Dropbox/persistent-team/projects/דירוג ערים קודם/gefen4/screenshots"

VIEWPORTS = [
    ("desktop", 1920, 1080),
    ("tablet",  768,  1024),
    ("mobile",  375,  812),
]

async def main():
    import os
    os.makedirs(OUT, exist_ok=True)

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(headless=True)

        for name, w, h in VIEWPORTS:
            print(f"\n--- {name} ({w}x{h}) ---")
            page = await browser.new_page(viewport={"width": w, "height": h})
            await page.goto(URL, wait_until="networkidle", timeout=120000)

            try:
                await page.wait_for_selector("#tableWrap table tbody tr", timeout=90000)
                print(f"  Table loaded")
            except Exception as e:
                print(f"  WARNING: {e}")
                await page.screenshot(path=f"{OUT}/{name}_error.png", full_page=True)
                await page.close()
                continue

            await asyncio.sleep(2)

            # 1) Initial with 20-cities ON (default)
            await page.screenshot(path=f"{OUT}/{name}_01_cities_on.png", full_page=False)
            rows = await page.query_selector_all("#tableBody tr")
            print(f"  20-cities ON: {len(rows)} rows")

            # 2) Toggle cities OFF to show all
            toggle = await page.query_selector("#citiesToggle")
            if toggle:
                await toggle.click()
                await asyncio.sleep(1.5)
                await page.screenshot(path=f"{OUT}/{name}_02_cities_off.png", full_page=False)
                rows2 = await page.query_selector_all("#tableBody tr")
                print(f"  20-cities OFF: {len(rows2)} rows")

                # Toggle back ON
                await toggle.click()
                await asyncio.sleep(1)

            # 3) Sort by budget
            budget_th = await page.query_selector('th[data-sort="budget"]')
            if budget_th:
                await budget_th.click()
                await asyncio.sleep(1)
                await page.screenshot(path=f"{OUT}/{name}_03_sort_budget.png", full_page=False)
                print(f"  Sorted by budget")

            # 4) Edit test (desktop only)
            if name == "desktop":
                desc = await page.query_selector('textarea[data-field="description"]')
                if desc:
                    await desc.click()
                    await desc.fill("בדיקה")
                    await asyncio.sleep(0.5)
                    await page.screenshot(path=f"{OUT}/{name}_04_edit.png", full_page=False)
                    save_btn = await page.query_selector('button.btn-save.visible')
                    print(f"  Edit test: save button {'visible' if save_btn else 'NOT visible'}")
                    await desc.fill("")

            # 5) Search
            search = await page.query_selector("#searchInput")
            if search:
                await search.fill("יזמות")
                await asyncio.sleep(1)
                await page.screenshot(path=f"{OUT}/{name}_05_search.png", full_page=False)
                rows3 = await page.query_selector_all("#tableBody tr")
                print(f"  Search 'יזמות': {len(rows3)} results")
                await search.fill("")
                await asyncio.sleep(0.5)

            await page.close()

        await browser.close()
        print(f"\nDone! Screenshots: {OUT}")

asyncio.run(main())
