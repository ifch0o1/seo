import selenium.webdriver as webdriver
from selenium.webdriver.support.ui import Select
import time
import pyperclip
import json

browser = webdriver.Chrome('C:\webdrivers\chromedriver')

def change_language(language):
    element = Select(browser.find_element_by_id('edit-country-language'))

    time.sleep(5)

    element.select_by_visible_text(language)


def get_results(language ,search_term):
    browser.get('https://keywordtool.io/')
    change_language(language)

    time.sleep(10)

    search_box = browser.find_element_by_name('keyword')
    search_box.send_keys(search_term)
    search_box.submit()

    time.sleep(30)

    select_all = browser.find_element_by_xpath('//*[@class="table table-search-results"]//th//input')
    select_all.click()

    time.sleep(10)

    copy_button = browser.find_element_by_name("copy_selected")
    copy_button.click()

    copied = pyperclip.paste()
    copied = copied.replace('\r',',').replace('\n','').split(',')

    
    print(copied)
    # with open('new.txt','w') as g:
    #     g.write(s)

get_results('United States / English', 'office paper')

    
# def get_results(search_term):
 
#     browser.get('https://keywordtool.io/')
#     search_box = browser.find_element_by_name('keyword')
#     search_box.send_keys(search_term)
#     search_box.submit()

#     time.sleep(30)

#     links = browser.find_elements_by_xpath('//table[@class="table table-search-results"]//td/span')
#     results = []
#     for link in links:
#         print(link)
#         results.append(link)
    
#     return results


# def next_page():
#     browser.find_element_by_xpath("//span[text()='Next']").click()
#     time.sleep(0.5)


# def previous_page():
#     browser.find_element_by_xpath("//span[text()='Previous']").click()
#     time.sleep(0.5)


# def get_current_page():
#     current_page = browser.find_elements_by_xpath("//*[@id='foot']//td/span/..")[0].text
#     return current_page




# get_results('dog')
# "next_page()
# print(get_current_page())"