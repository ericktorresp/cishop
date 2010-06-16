
# coding=utf-8
from django.shortcuts import render_to_response
from django.template import RequestContext, Context, Template
from django.http import Http404, HttpResponse
from django.contrib.auth.models import User
from account.models import UserProfile
from django.utils.translation import ugettext as _

try:
    import json
except:
    import simplejson as json

class home:
    def __call__(self, request):
        return render_to_response('index.html', {}, context_instance = RequestContext(request))

CITY_BUILDINGS = [
    {'title':'Hospital', 'cls':['spot1', 'cityhospital'], 'type':'a'},
    {'title':'Casino', 'cls':['spot2', 'casino'], 'type':'a'},
    {'title':'PD', 'cls':['spot3', 'pd'], 'type':'a'},
    {'title':'Bank', 'cls':['spot4', 'bank'], 'type':'a'},
    {'title':'Fitness and School', 'cls':['spot5', 'fitness'], 'type':'a'},
    {'title':'Jail', 'cls':['spot6', 'prison'], 'type':'a'},
    {'title':'Black Market', 'cls':['spot7', 'blackmarket'], 'type':'a'},
    {'title':'fence', 'cls':['fence'], 'type':'div'}
]
CITY_NPC = [
    {'title':'Patrol officer', 'cls':['patrolcar'], 'type':'a'},
    {'title':'Drunkard', 'cls':['drunk'], 'type':'a'},
    {'title':'Mailbox', 'cls':['post'], 'type':'a'},
    {'title':'Nurse', 'cls':['sickboy'], 'type':'a'},
    {'title':'Coffee Machine', 'cls':['cofemachine'], 'type':'a'},
    {'title':'Housewife', 'cls':['housewife'], 'type':'a'},
    {'title':'No entry', 'cls':['sign'], 'type':'a'},
    {'title':'One way', 'cls':['sign2'], 'type':'a'},
    {'title':'No entry', 'cls':['sign3'], 'type':'a'},
    {'title':'Gents', 'cls':['uncles'], 'type':'a'},
    {'title':'Chick', 'cls':['girl'], 'type':'a'},
    {'title':'Family', 'cls':['family'], 'type':'a'},
    {'title':'Smart fellows', 'cls':['meeting'], 'type':'a'},
    {'title':'Phonebooth', 'cls':['phone'], 'type':'a'},
    {'title':'Ambulance', 'cls':['ambulance'], 'type':'a'},
    {'title':'Biker', 'cls':['bicyclist'], 'type':'a'},
    {'title':'Safe-cracker', 'cls':['safecracker'], 'type':'a'},
    {'title':'Salesman', 'cls':['merchant'], 'type':'a'},
    {'title':'Yellow cab', 'cls':['taxi'], 'type':'a'},
    {'title':'Robbery', 'cls':['robbery'], 'type':'a'},
    {'title':'Business lady', 'cls':['businesslady'], 'type':'a'},
    {'title':'Thief', 'cls':['thief'], 'type':'a'},
    {'title':'Mobster', 'cls':['bully'], 'type':'a'},
    {'title':'Pimp', 'cls':['pimp'], 'type':'a'},
    {'title':'Police hound', 'cls':['policedog'], 'type':'a'},
    {'title':'Dealer', 'cls':['dealer'], 'type':'a'},
    {'title':'Jeep', 'cls':['bmw'], 'type':'a'},
]
class map:
    """
    返回json编码后的数组，包含街区座标，街区建筑，街区NPC，以及街区玩家
    街区玩家如何获取？
    APE 与 Django 之间的 session 如何同步？
    流程： 页面载入 - 登录到 APE - [是否可请求某个 django 页面，以获取 session id?]
    """
    def __call__(self, request):
        objects = []
        profiles = UserProfile.objects.order_by('-street_y', 'street_x')[:9]
        for profile in profiles:
            street = {
                'x': profile.street_x,
                'y': profile.street_y
            }
            if profile.street_x == 0 and profile.street_y == 0:
                # city
                street['buildings'] = CITY_BUILDINGS
                street['NPC'] = CITY_NPC
            objects.append(street)

        if request.GET.has_key('x'):
            print('x=', request.GET['x'])
        if request.GET.has_key('y'):
            print('y=', request.GET['y'])
        if request.GET.has_key('dir'):
            print request.GET['dir'] == ''
            print('dir=', request.GET['dir'])
        if objects:
            encoded = json.dumps(objects)
            response = HttpResponse(encoded, mimetype = "application/json")
            return response

    def _get_maps(self):
        pass
