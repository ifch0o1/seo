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

driver = get_seciruty_expose_cdp_driver()

lang = str(sys.argv[2])

# # Server connection
# if str(sys.argv).count('local') > 0:
#     driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.
#     # driver = webdriver.Firefox('/var/www/html/seo/SEO_py/')
#     apiUrl = 'http://79.124.39.68/api/push_python_words'
# else:
#     driver = webdriver.Remote(
#         command_executor='http://127.0.0.1:4444/wd/hub', 
#         desired_capabilities=DesiredCapabilities.CHROME)
#     apiUrl = 'http://seo.maxprogress.bg/api/push_python_words'
    
# driver.maximize_window()

def change_language(language):
    xpath  = f"//option[contains(text(), '{language}')]"
    print(xpath)
    optionToSelect = WebDriverWait(driver, 15).until(EC.presence_of_element_located((By.XPATH, xpath)))
    optionToSelect.click()
    time.sleep(0.5)


def get_results(language ,search_term):
    driver.get('https://keywordtool.io/')
    time.sleep(15)
    
    change_language(language)

    search_box = driver.find_element_by_name('keyword')
    search_box.send_keys(search_term)
    search_box.submit()

    time.sleep(30)
    
    try:
        wrapper = WebDriverWait(driver, 60).until(
            EC.visibility_of_element_located((By.ID, "content"))
        )
    finally:
        print('something went wrong. no #content element presented.')
        # driver.quit()
    
    select_all = WebDriverWait(driver, 15).until(EC.presence_of_element_located((By.XPATH, '//*[@class="table table-search-results"]//th//input')))
    
    time.sleep(30)
    
    select_all.click()

    time.sleep(5)

    copy_button = driver.find_element_by_name("copy_selected")
    copy_button.click()
    
    time.sleep(3)

    copied = pyperclip.paste()
    copied = copied.replace('\n',',').split(',')
    
    print(copied)

get_results(lang, base_keyword)

driver.quit()