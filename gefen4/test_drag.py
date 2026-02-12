"""Quick test: drag handle visible + drag-scroll works."""
import asyncio
from playwright.async_api import async_playwright

URL = "https://gnostocracy.com/ranking/gefen4/"
OUT = "/Users/zvishalem/Library/CloudStorage/Dropbox/persistent-team/projects/דירוג ערים קודם/gefen4/screenshots"

async def main():
    import os
    os.makedirs(OUT, exist_ok=True)

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1920, "height": 1080})
        await page.goto(URL, wait_until="networkidle", timeout=120000)

        await page.wait_for_selector("#tableWrap table tbody tr", timeout=90000)
        await asyncio.sleep(2)

        # Screenshot initial (top of table with drag handle at bottom)
        await page.screenshot(path=f"{OUT}/drag_01_initial.png", full_page=False)
        print("1. Initial screenshot taken")

        # Check drag handle exists
        handle = await page.query_selector("#dragHandle")
        if handle:
            box = await handle.bounding_box()
            print(f"2. Drag handle found at y={box['y']:.0f}, height={box['height']:.0f}")
        else:
            print("2. WARNING: Drag handle NOT found!")
            await browser.close()
            return

        # Scroll down to see drag handle
        await page.evaluate("document.getElementById('tableWrap').scrollTop = 99999")
        await asyncio.sleep(0.5)
        await page.screenshot(path=f"{OUT}/drag_02_scrolled.png", full_page=False)
        print("3. Scrolled to bottom screenshot")

        # Simulate drag on the handle: grab and drag left 300px
        box = await handle.bounding_box()
        start_x = box['x'] + box['width'] / 2
        start_y = box['y'] + box['height'] / 2

        # Record initial scroll position
        scroll_before = await page.evaluate("document.getElementById('tableWrap').scrollLeft")
        print(f"4. Scroll position before drag: {scroll_before}")

        await page.mouse.move(start_x, start_y)
        await page.mouse.down()
        await page.mouse.move(start_x - 300, start_y, steps=10)
        await asyncio.sleep(0.3)
        await page.screenshot(path=f"{OUT}/drag_03_dragging.png", full_page=False)
        await page.mouse.up()

        scroll_after = await page.evaluate("document.getElementById('tableWrap').scrollLeft")
        print(f"5. Scroll position after drag: {scroll_after}")
        print(f"   Delta: {scroll_after - scroll_before}px (expected ~300)")

        await page.screenshot(path=f"{OUT}/drag_04_after.png", full_page=False)
        print("6. Final screenshot taken")

        await browser.close()
        print(f"\nDone! Screenshots in {OUT}")

asyncio.run(main())
