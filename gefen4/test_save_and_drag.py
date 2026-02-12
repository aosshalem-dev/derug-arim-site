"""Test drag handle + save interaction end-to-end."""
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

        # Listen for console errors
        errors = []
        page.on("console", lambda msg: errors.append(msg.text) if msg.type == "error" else None)

        await page.goto(URL, wait_until="networkidle", timeout=120000)
        await page.wait_for_selector("#tableWrap table tbody tr", timeout=90000)
        await asyncio.sleep(3)

        print("=== DRAG HANDLE TEST ===")
        handle = await page.query_selector("#dragHandle")
        if handle:
            box = await handle.bounding_box()
            visible = box and box['y'] < 1080 and box['y'] >= 0
            print(f"  Handle position: y={box['y']:.0f}, visible in viewport: {visible}")
            await page.screenshot(path=f"{OUT}/save_01_with_handle.png", full_page=False)

            if visible:
                # Drag test
                cx = box['x'] + box['width'] / 2
                cy = box['y'] + box['height'] / 2
                scroll_before = await page.evaluate("document.getElementById('tableWrap').scrollLeft")
                await page.mouse.move(cx, cy)
                await page.mouse.down()
                await page.mouse.move(cx - 400, cy, steps=15)
                await asyncio.sleep(0.3)
                await page.mouse.up()
                scroll_after = await page.evaluate("document.getElementById('tableWrap').scrollLeft")
                delta = scroll_after - scroll_before
                print(f"  Drag scroll: {scroll_before} -> {scroll_after} (delta: {delta})")
                await page.screenshot(path=f"{OUT}/save_02_after_drag.png", full_page=False)

                # Scroll back
                await page.evaluate("document.getElementById('tableWrap').scrollLeft = 0")
                await asyncio.sleep(0.5)
        else:
            print("  WARNING: Drag handle not found!")

        print("\n=== SAVE INTERACTION TEST ===")

        # Find first row and its program ID
        first_row = await page.query_selector("#tableBody tr")
        pid = await first_row.get_attribute("data-pid")
        print(f"  Testing with program: {pid}")

        # 1) Type in description
        desc_ta = await first_row.query_selector('textarea[data-field="description"]')
        original_val = await desc_ta.input_value()
        test_text = "בדיקת שמירה אוטומטית - " + str(int(asyncio.get_event_loop().time()))

        await desc_ta.click()
        await desc_ta.fill(test_text)
        await asyncio.sleep(0.5)

        # 2) Check save button appeared
        save_btn = await first_row.query_selector('button.btn-save.visible')
        has_changes = "has-changes" in (await first_row.get_attribute("class") or "")
        print(f"  After edit: save button={'visible' if save_btn else 'hidden'}, row highlighted={has_changes}")
        await page.screenshot(path=f"{OUT}/save_03_edited.png", full_page=False)

        # 3) Click save
        if save_btn:
            print("  Clicking save...")
            await save_btn.click()
            await asyncio.sleep(3)

            # Check status
            status = await page.query_selector(f"#status-{pid}")
            status_text = await status.inner_text() if status else "N/A"
            print(f"  Save status: '{status_text}'")

            # Check row no longer highlighted
            has_changes_after = "has-changes" in (await first_row.get_attribute("class") or "")
            save_btn_after = await first_row.query_selector('button.btn-save.visible')
            print(f"  After save: row highlighted={has_changes_after}, save button={'visible' if save_btn_after else 'hidden'}")

            await page.screenshot(path=f"{OUT}/save_04_saved.png", full_page=False)

            # 4) Verify by reloading and checking the value persisted
            print("  Reloading page to verify persistence...")
            await page.reload(wait_until="networkidle", timeout=120000)
            await page.wait_for_selector("#tableWrap table tbody tr", timeout=90000)
            await asyncio.sleep(3)

            # Find the same program's description
            reloaded_ta = await page.query_selector(f'textarea[data-field="description"][data-pid="{pid}"]')
            if reloaded_ta:
                persisted_val = await reloaded_ta.input_value()
                match = test_text in persisted_val
                print(f"  Persisted value matches: {match}")
                print(f"  Expected: '{test_text}'")
                print(f"  Got: '{persisted_val[:80]}'")
            else:
                print(f"  WARNING: Could not find textarea for program {pid} after reload")

            await page.screenshot(path=f"{OUT}/save_05_reloaded.png", full_page=False)

            # 5) Restore original value
            if reloaded_ta:
                print("  Restoring original value...")
                await reloaded_ta.click()
                await reloaded_ta.fill(original_val)
                await asyncio.sleep(0.5)
                restore_row = await page.query_selector(f'tr[data-pid="{pid}"]')
                restore_btn = await restore_row.query_selector('button.btn-save.visible') if restore_row else None
                if restore_btn:
                    await restore_btn.click()
                    await asyncio.sleep(3)
                    restore_status = await page.query_selector(f"#status-{pid}")
                    print(f"  Restore status: '{await restore_status.inner_text() if restore_status else 'N/A'}'")
        else:
            print("  ERROR: Save button not visible after edit!")

        # Report errors
        if errors:
            print(f"\n  Console errors: {errors}")
        else:
            print(f"\n  No console errors")

        await browser.close()
        print(f"\nAll screenshots in {OUT}")

asyncio.run(main())
