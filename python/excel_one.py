#date 2020.1.17
#autor by 磊爷
#desc  read excel to wordpress
import xlsxwriter, xlrd
import random
from wordpress_xmlrpc import Client, WordPressPost
from wordpress_xmlrpc.methods.posts import GetPosts, NewPost
from wordpress_xmlrpc.methods.users import GetUserInfo
from wordpress_xmlrpc.methods import posts
from wordpress_xmlrpc.methods import taxonomies
from wordpress_xmlrpc import WordPressTerm
from wordpress_xmlrpc.compat import xmlrpc_client
from wordpress_xmlrpc.methods import media, posts
import importlib, sys
import datetime
import time
importlib.reload(sys)
wp = Client('http://www.qdhanzhang.com/xmlrpc.php', 'w412241071', 'zhongwei')
post = WordPressPost()

data = xlrd.open_workbook('hello.xlsx')
# 查看所有工作表
data.sheet_names()
#print("sheets：" + str(data.sheet_names()))
#查出来有[sheet1] 这一个数组元素，所以直接by_index(0)了
table = data.sheet_by_index(0)
print("总行数：" + str(table.nrows))
print("总列数：" + str(table.ncols))
#遍历
nrows = table.nrows
cols = table.ncols
datetimestr = int(time.time())-10*3600   #他大爷的不知道为什么服务器快8个小时，，， 先减去10个小时
#print(t)
print('当前时间', datetimestr)
timelen = 30    #每篇文章相差的时间  秒
for i in range(nrows):
    rowcont = table.row_values(i)
    #print(rowcont[1])
    #post.title = rowcont[0]  #标题
    cont = ''
    for k in range(cols):
        if k < 2:
            continue
        cont = cont + str(rowcont[k])
    #print(cont)
    post.content = cont  #内容
    datastr = datetimestr + (i + 1) * timelen - timelen
    print('执行时间', datastr)
    post.date = datastr

    #rowcont1 是标签
    lastd = rowcont[1][len(rowcont[1]) - 1]
    if lastd == ',':
        bqstr = rowcont[1][0:len(rowcont[1]) - 1]  #截取
    else:
        bqstr = rowcont[1]
    #print(laststr)

    #name_list = bqstr.split(',')  #分割
    #title = random.sample(name_list, 2)
    #title = ','.join(title)
    post.title = rowcont[0]  #标题
    #print(title)
    laststr = rowcont[1][len(rowcont[1]) - 1]
    #print(laststr)
    flagstr = rowcont[1][0:len(rowcont[1]) - 1]  #截取

    if laststr == ',':
        flag = flagstr.split(',')
    else:
        if ',' in rowcont[1]:
            flag = rowcont[1].split(',')
        else:
            flag = [rowcont[1]] 

    #print(flag)
    post.terms_names = {
        'post_tag': flag,  #文章所属标签，没有则自动创建
    }
    post.post_status = 'publish'
    post.id = wp.call(posts.NewPost(post))
