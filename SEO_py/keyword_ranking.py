#!/usr/bin/env python

import time
import sys
from selenium import webdriver
from selenium.webdriver.common.desired_capabilities import DesiredCapabilities
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.support.ui import WebDriverWait
# from selenium.common.exceptions import TimeoutException
import random
import json
import requests
from urllib.parse import urlparse


def get_domain(url):
    return urlparse(url)

cron = str(sys.argv).count('cron') > 0

if cron:
    apiUrl = 'https://seo.maxprogress.bg/api/keyword-ranking-words'
    driver = webdriver.Remote(
        command_executor='http://127.0.0.1:4444/wd/hub',
        desired_capabilities=DesiredCapabilities.CHROME)
else:
    apiUrl = 'http://79.124.36.172/api/keyword-ranking-words'
    driver = webdriver.Chrome('/var/www/html/seo/SEO_py/chromedriver')  # Optional argument, if not specified will search path.

page_turns = 6

driver.get('http://www.google.com/')
time.sleep(random.randint(5, 35))

def find_position(keyword, site):
    site = get_domain(site).netloc
    if site == '':
        return False

    search_box = WebDriverWait(driver, 5).until(
        EC.presence_of_element_located((By.NAME, "q"))
    )

    search_box.clear()
    search_box.send_keys(keyword)
    search_box.submit()

    link_results = [] # not used for now.

    x = range(page_turns)
    current_site_index = False
    for n in x:
        print('searching page', n)
        link_list = get_results()
        link_results += link_list

        for index, l in enumerate(link_results):
            if l.count(site) > 0:
                current_site_index = index
                break
        
        if current_site_index:
            break
        else:
            # Clicking next_page() - if next page return false - no next page button exists (no more pages available)
            if next_page() == False:
                return False

    if current_site_index != False:
        return {'position': current_site_index + 1, 'url': link_results[current_site_index]}
    else:
        return False

def next_page():
    try:
        next_page_link = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.XPATH, "//*[@id='pnnext']"))
        )
        next_page_link.click()
        time.sleep(random.randint(5, 35))
        return True
    except:
        print('next page error')
        return False

def previous_page():
    try:
        prev_page_link = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.XPATH, "//*[@id='pnprev']"))
        )
        prev_page_link.click()
        time.sleep(random.randint(5, 35))
        return True
    except:
        print('prev page error')
        return False

def get_current_page():
    try:
        current_page = WebDriverWait(driver, 5).until(
            EC.presence_of_element_located((By.XPATH, "//*[@id='foot']//td/span/.."))
        )
        return current_page.text
    except:
        print('current page error')
        return False

def get_results():
    results_xpath = '//*[@id="search"]//*[@class="g"]//h3/../../a'

    try:
        links = WebDriverWait(driver, 5).until(
            EC.presence_of_all_elements_located((By.XPATH, results_xpath))
        )
    except:
        # Wait ~60-80 minutes until google removed a captcha.
        time.sleep(4500)
        driver.get('http://www.google.com/')
        return []

    results = []
    for link in links:
        href = link.get_attribute('href')
        # print(href)
        results.append(href)
    
    return results

r = requests.get(apiUrl)

data = json.loads(r.text)

for href_data in data:
    posData = find_position(href_data['keyword'], href_data['site'])
    if posData:
        position = posData['position']
        url = posData['url']

        store_r = requests.post(apiUrl, json={
            'keyword_id': href_data['keyword_id'],
            'client_id': href_data['client_id'],
            'position': position,
            'link': url
        })

        print(store_r.status_code)

driver.quit()