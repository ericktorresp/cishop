from django.shortcuts import render_to_response
from django.template import RequestContext, Context, Template
from django.http import Http404, HttpResponse

class home:
    def __call__(self, request):
        return render_to_response('index.html', {}, context_instance=RequestContext(request))

JSON_TEMPLATE = u"[{% for obj in objects %}{x:'{{ obj.x }}',y:'{{ obj.y }}'}{% if not forloop.last %},{% endif %}{% endfor %}]"

class map:
    def __call__(self, request):
        objects = [
            {'x':-1,'y':1},
            {'x':0,'y':1},
            {'x':1,'y':1},
            {'x':-1,'y':0},
            {'x':0,'y':0},
            {'x':1,'y':0},
            {'x':-1,'y':-1},
            {'x':0,'y':-1},
            {'x':1,'y':-1}
        ]
        if request.GET.has_key('x'):
            print('x=',request.GET['x'])
        if request.GET.has_key('y'):
            print('y=',request.GET['y'])
        if request.GET.has_key('dir'):
            print('dir=',request.GET['dir'])
        if objects:
            t = Template(JSON_TEMPLATE)
            c = Context({'objects': objects})
            return HttpResponse(t.render(c), mimetype='text/javascript; charset=utf-8')