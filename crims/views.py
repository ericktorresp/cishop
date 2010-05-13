from django.shortcuts import render_to_response
from django.template import RequestContext

class home:
    def __call__(self, request):
        return render_to_response('index.html', {}, context_instance=RequestContext(request))
