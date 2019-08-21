namespace php Rpc.server
include '../struct/Response.thrift'

service UserService{
    Response.Response uadd(1: string params);
    Response.Response uindex(1: string params);
    Response.Response uupdate(1: string params);
}