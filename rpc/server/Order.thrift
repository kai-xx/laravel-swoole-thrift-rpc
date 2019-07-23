namespace php Rpc.server
include '../struct/Response.thrift'

service OrderService{
    Response.Response add(1: string params);
    Response.Response index(1: string params);
    Response.Response update(1: string params);
}