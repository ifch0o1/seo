import selenium.webdriver as webdriver
from selenium.webdriver.support.ui import Select
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
import time
import pyperclip
import json
import sys

from cdc_expose import get_seciruty_expose_cdp_driver

from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait

base_keyword = str(sys.argv[1])
base_keyword = base_keyword.replace("_", " ")
lang = str(sys.argv[2])

# base_keyword = 'женски обувки'
# lang = 'BG:bg'

# driver = get_seciruty_expose_cdp_driver()
time.sleep(5.5)

def get_new_driver():
    # # Server connection
    if str(sys.argv).count('local') > 0:
        # driver = webdriver.Firefox('/var/www/html/seo/SEO_py/')
        newDriver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.
        newDriver.set_window_size(1920, 1080, newDriver.window_handles[0])
        
        return newDriver
    else:
        newDriver = webdriver.Remote(
            command_executor='http://127.0.0.1:4444/wd/hub', 
            desired_capabilities=DesiredCapabilities.CHROME)
        
        newDriver.set_window_size(1920, 1080, newDriver.window_handles[0])
        
        return newDriver
    
# driver.maximize_window()

def change_language(language, useDriver):
    xpath  = f"//option[@value='{language}']"

    optionToSelect = WebDriverWait(useDriver, 15).until(EC.presence_of_element_located((By.XPATH, xpath)))
    optionToSelect.click()
    time.sleep(0.5)


def get_results(language ,search_term, useDriver):
    useDriver.get('https://keywordtool.io/')
    time.sleep(13)
    
    change_language(language, useDriver)

    search_box = useDriver.find_element_by_name('keyword')
    search_box.send_keys(search_term)
    search_box.submit()

    time.sleep(17)
    
    useDriver.get_screenshot_as_file("screenshot1.png")
    
    try:
        searchForSuccessElement = WebDriverWait(useDriver, 25).until(
            EC.presence_of_element_located((By.ID, "branding"))
        )
    except Exception as e:
        useDriver.get_screenshot_as_file("screenshot2.png")
        useDriver.quit()
        
        return get_results(language, search_term, get_new_driver())
    
        print('something went wrong. no #content element presented.')
        # driver.quit()

    time.sleep(5)
    
    useDriver.get_screenshot_as_file("screenshot3.png")
    
    time.sleep(10)

    useDriver.get_screenshot_as_file("screenshot4.png")
    
    time.sleep(3)
    
    text_area_with_all_words = WebDriverWait(useDriver, 45).until(
            EC.presence_of_all_elements_located((By.NAME, "copy_all"))
        )
    
    keywords = ''
    for textarea in text_area_with_all_words:
        text_area_value = textarea.get_attribute('value')
        if len(text_area_value) > 0:
            keywords = text_area_value
            break

    keywords = keywords.replace('\n',',').split(',')
    
    print(json.dumps(keywords))

get_results(lang, base_keyword, get_new_driver())