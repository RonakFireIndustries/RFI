export const unwrapList = (payload) => {
  if (Array.isArray(payload)) return payload;
  if (Array.isArray(payload?.data)) return payload.data;
  if (Array.isArray(payload?.data?.data)) return payload.data.data;
  
  if (payload && typeof payload === 'object') {
    for (const key of Object.keys(payload)) {
      const val = payload[key];
      if (Array.isArray(val)) {
        return val;
      }
      if (val && typeof val === 'object' && Array.isArray(val.data)) {
        return val.data;
      }
    }
  }
  return [];
};

export const unwrapItem = (payload) => {
  if (payload?.data && !Array.isArray(payload.data)) return payload.data;
  
  if (payload && typeof payload === 'object' && !Array.isArray(payload)) {
    const keys = Object.keys(payload);
    if (keys.length === 1 && typeof payload[keys[0]] === 'object' && !Array.isArray(payload[keys[0]])) {
      return payload[keys[0]];
    }
  }
  
  return payload;
};

export const normalizeQuery = (params = {}) => {
  const query = new URLSearchParams();

  const finalParams = { per_page: 1000, ...params };

  Object.entries(finalParams).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      query.set(key, value);
    }
  });

  const serialized = query.toString();
  return serialized ? `?${serialized}` : '';
};
