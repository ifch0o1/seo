#!/usr/bin/env python

import time
import sys
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
import json
import requests

# GET FROM REQUEST
# symbols = "qwertyuiopasdfghjklzxcvbnm1234567890"
# Request parameters
# symbols = "абвгдежзийклмнопстуфхцчшщюя"

base_keyword = str(sys.argv[1])
base_keyword = base_keyword.replace("_", " ")
request_level = int(sys.argv[2])
symbols = str(sys.argv[3]).encode('utf-8', 'surrogateescape').decode('utf-8')
if sys.argv[4]:
    industry = int(sys.argv[4])
else:
    industry = ''

# Server connection
if str(sys.argv).count('local') > 0:
    driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.
else:
    driver = webdriver.Remote(
        command_executor='http://127.0.0.1:4444/wd/hub', 
        desired_capabilities=DesiredCapabilities.CHROME)
driver.get('http://www.google.com/')

# Elements
search_box = driver.find_element_by_name('q')

def delete_input():
    search_box.send_keys(Keys.CONTROL + "a")
    search_box.send_keys(Keys.DELETE)

def delete_last_word():
    search_box.send_keys(Keys.CONTROL + Keys.SHIFT + Keys.ARROW_LEFT)
    search_box.send_keys(Keys.DELETE)

# Samo vrashta array s suggestionite
def get_suggestions(keyword):
    # keyword is used only for checking if suggestion have the full keyword inside the text
    # sometimes google returns only single words and actually cuts the full keyword
    array = []
    try:
        WebDriverWait(driver, 3).until(EC.visibility_of_element_located((By.CSS_SELECTOR, '[role="listbox"]')))
    except:
        return array

    for li in driver.find_elements_by_css_selector('[role="listbox"] > li'):
        text = li.text.strip()
        if len(text) > 0:
            if text.lower().count(keyword.lower()) == 0:
                if (text.count(' ') <= 1):
                    text = keyword + ' ' + text
            array.append(text)

    return array

def process_keyword_with_symbols(keyword, level = 1):
    keyword = keyword.encode('utf-8', 'surrogateescape').decode('utf-8')
    # Init function
    delete_input()
    try:
        search_box.send_keys(keyword + ' ')
    except:
        print("KEYWORD: " + keyword)

    # see the recursion below
    keyword_object = {}
    
    for c in symbols:
        time.sleep(0.8)
        search_box.send_keys(c)
        time.sleep(1.2)

        suggestions = get_suggestions(keyword)
        # recursion for.
        for sug in suggestions:
            children = {}
            if level <= request_level:
                children = process_keyword_with_symbols(sug, level + 1)
            
            keyword_object[sug] = {'level': level, 'children': children, 'name': sug}
            
        delete_last_word()
    return keyword_object

result = process_keyword_with_symbols(base_keyword)

r = requests.post('http://www.seo-tracktor.com/api/push_python_words',
                    # data=json.dumps({'keywords_json': result})
                    json={'keywords_json': result, 'industry': industry},
                    headers={'Content-Type': 'application/json; charset=utf-8'})

print("Sending request to php... The response is:")

print(r.text)

# print(json.dumps(result))

driver.quit()
