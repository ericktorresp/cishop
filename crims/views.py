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

class map:
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
                street['buildings'] = [
                    {'title':_('Hospital'), 'cls':['spot1', 'cityhospital'], 'type':'a'},
                    {'title':_('Casino'), 'cls':['spot2', 'casino'], 'type':'a'},
                    {'title':_('PD'), 'cls':['spot3', 'pd'], 'type':'a'},
                    {'title':_('Bank'), 'cls':['spot4', 'bank'], 'type':'a'},
                    {'title':_('Fitness and School'), 'cls':['spot5', 'fitness'], 'type':'a'},
                    {'title':_('Jail'), 'cls':['spot6', 'prison'], 'type':'a'},
                    {'title':_('Black Market'), 'cls':['spot7', 'blackmarket'], 'type':'a'},
                    {'title':_('fence'), 'cls':['fence'], 'type':'div'}
                ]
                street['NPC'] = [
                    {'title':_('Patrol officer'), 'cls':['patrolcar'], 'type':'a'},
                    {'title':_('Drunkard'), 'cls':['drunk'], 'type':'a'},
                    {'title':_('Mailbox'), 'cls':['post'], 'type':'a'},
                    {'title':_('Nurse'), 'cls':['sickboy'], 'type':'a'},
                    {'title':_('Coffee Machine'), 'cls':['cofemachine'], 'type':'a'},
                    {'title':_('Housewife'), 'cls':['housewife'], 'type':'a'},
                    {'title':_('No entry'), 'cls':['sign'], 'type':'a'},
                    {'title':_('One way'), 'cls':['sign2'], 'type':'a'},
                    {'title':_('No entry'), 'cls':['sign3'], 'type':'a'},
                    {'title':_('Gents'), 'cls':['uncles'], 'type':'a'},
                    {'title':_('Chick'), 'cls':['girl'], 'type':'a'},
                    {'title':_('Family'), 'cls':['family'], 'type':'a'},
                    {'title':_('Smart fellows'), 'cls':['meeting'], 'type':'a'},
                    {'title':_('Phonebooth'), 'cls':['phone'], 'type':'a'},
                    {'title':_('Ambulance'), 'cls':['ambulance'], 'type':'a'},
                    {'title':_('Biker'), 'cls':['bicyclist'], 'type':'a'},
                    {'title':_('Safe-cracker'), 'cls':['safecracker'], 'type':'a'},
                    {'title':_('Salesman'), 'cls':['merchant'], 'type':'a'},
                    {'title':_('Yellow cab'), 'cls':['taxi'], 'type':'a'},
                    {'title':_('Robbery'), 'cls':['robbery'], 'type':'a'},
                    {'title':_('Business lady'), 'cls':['businesslady'], 'type':'a'},
                    {'title':_('Thief'), 'cls':['thief'], 'type':'a'},
                    {'title':_('Mobster'), 'cls':['bully'], 'type':'a'},
                    {'title':_('Pimp'), 'cls':['pimp'], 'type':'a'},
                    {'title':_('Police hound'), 'cls':['policedog'], 'type':'a'},
                    {'title':_('Dealer'), 'cls':['dealer'], 'type':'a'},
                    {'title':_('Jeep'), 'cls':['bmw'], 'type':'a'},
                ]
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
