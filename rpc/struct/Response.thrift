namespace php Rpc.struct

struct Response {
    1: i32 code;
    2: string message;
    3: string data;
}
